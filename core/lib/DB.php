<?php

namespace ICT\Core;

/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *        
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

class DB
{

  static $link = NULL;

  static function connect($link_new = FALSE)
  {
    $db_port = Conf::get('db:port', '3306');
    $db_host = Conf::get('db:host', 'localhost') . ':' . $db_port;
    $db_user = Conf::get('db:user', 'myuser');
    $db_pass = Conf::get('db:pass', '');
    $db_name = Conf::get('db:name', 'ictcore');

    $link = mysqli_connect($db_host, $db_user, $db_pass);
    if (!$link) {
      throw new CoreException('500', 'Unable to connect database server error:' . mysqli_error($link));
    }
    $result = mysqli_select_db($link, $db_name);
    if (!$result) {
      throw new CoreException('500', 'Unable to select database');
    }
    mysqli_query($link, "SET time_zone = '+00:00'"); // required to bypass server timezone settings

    return $link;
  }

  static function next_record_id($table, $field = '')
  {
    $result = mysqli_query(DB::$link, "SELECT sequence FROM sequence WHERE table_name='$table'");
    if (mysqli_num_rows($result)) {
      while($row = mysqli_fetch_assoc($result)) {
        $newid = $row['sequence'] + 1;
        mysqli_query(DB::$link, "UPDATE sequence SET sequence = $newid WHERE table_name = '$table'");
        return $newid;
      }
    } else {
      $field = $field ? $field : $table . '_id';
      $result = mysqli_query(DB::$link, "SELECT MAX($field) as newid FROM $table");
      if ($result) {
        while($row = mysqli_fetch_assoc($result)) {
          $col_newid = $row['newid'];
          $newid = $col_newid ? $col_newid + 1 : 1;
        }
      } else {
        $newid = 0;
      }
      $newid = $newid ? $newid + 1 : 1;
      mysqli_query(DB::$link, "INSERT INTO sequence (table_name, sequence) VALUES ('$table', $newid)");
      return $newid;
    }
  }

  static function save_record_id($table, $index)
  {
    $result = mysqli_query(DB::$link, "SELECT sequence FROM sequence WHERE table_name='$table'");
    if (mysqli_num_rows($result)) {
      mysqli_query(DB::$link, "UPDATE sequence SET sequence=$index WHERE table_name='$table'");
    } else {
      mysqli_query(DB::$link, "INSERT INTO sequence (table_name, sequence) VALUES ('$table', $index)");
    }
  }

  static function getSelectData($DBName, $DBField, $allData = true, $DBOrderField = '0', $check_auth = FALSE)
  {
    if ($DBOrderField == '0') {
      $DBOrderField = $DBField;
    }

    if ($allData == true) {
      $whereCondition = " WHERE %auth_filter%";
    } else {
      $whereCondition = " WHERE " . $DBName . "_id <> 0 AND %auth_filter%";
    }

    $query = "
            SELECT " . $DBName . "_id, " . $DBField . " AS name
            FROM  " . $DBName . " " . $whereCondition . "
            ORDER BY $DBName.$DBOrderField";

    return self::getSelectData_custom($DBName, $query, $check_auth);
  }

  static function getSelectData_custom($DBTable, $query, $check_auth = FALSE)
  {

    $result = self::query($DBTable, $query, array(), $check_auth);
    $aRecord = array();
    while ($record = mysqli_fetch_array($result)) {
      if (!empty($record[1]) && !is_null($record[1])) {
        $id = $record[0];
        $aRecord[$id] = $record[1];
      }
    }

    return $aRecord;
  }

  static function column_list($table)
  {
    $aColumn = array();
    $result = mysqli_query(DB::$link, "SHOW COLUMNS FROM $table");
    if ($result === FALSE) {
      Corelog::log("DB:unknown table: $table: " . mysqli_error(DB::$link), Corelog::ERROR);
      return FALSE;
    }
    while ($column = mysqli_fetch_assoc($result)) {
      $column_name = $column['Field'];
      $aColumn[$column_name] = $column;
    }
    return $aColumn;
  }

  static function column_map($aColumn, $aData)
  {
    static $non_text = array('bit', 'bigint', 'bool', 'boolean',
        'dec', 'decimal', 'double', 'float',
        'int', 'integer', 'mediumint', 'real',
        'smallint', 'tinyint', 'function'
    );
    $aMap = array(); // an item will consist array('field' => column_id, 'value' => 'something', is_string = false, 'default' = NULL);
    // Field      | Type     | Null | Key | Default | Extra  ==> Value
    foreach ($aColumn as $column_name => $column) {
      if (array_key_exists($column_name, $aData) && $aData[$column_name] !== NULL) {
        $type = strtolower(substr($column['Type'], 0, strpos($column['Type'], '(')));
        $aMap[$column_name] = array(
            'name' => $column['Field'],
            'value' => mysqli_real_escape_string(DB::$link, $aData[$column_name]),
            'is_string' => !in_array(trim($type), $non_text), // treat as string field not is a number
            'default' => $column['Default']
        );
      }
    }

    return $aMap;
  }

  static function where($table, $conditions, $glue = 'AND', $start = 'WHERE')
  {
    $aColumn = self::column_list($table);
    $aFilter = self::column_map($aColumn, $conditions);

    $cond = array();
    foreach ($aFilter as $field => $filter) {
      $value = $filter['value'];
      if (is_numeric($value)) {
        $cond[] = "$field = $value";
      } else if (strpos($value, 'auth_filter') !== false) {
        $cond[] = "$value";
      } else if (strpos($value, '%') !== false) {
        $cond[] = "$field LIKE '$value'";
      } else if (strpos($value, '=') !== false || strpos($value, '>') !== false || strpos($value, '<') !== false) {
        $cond[] = "$field $value";
      } else {
        $cond[] = "$field = '$value'";
      }
    }
    $cond_str = implode(" $glue ", $cond);
    if ($cond_str != '') {
      return trim("$start ($cond_str)");
    }
    return '';
  }

  static function update($table, &$values, $primary_key = FALSE, $check_auth = FALSE, $foreign_table = '', $foreign_key = '')
  {
    static $non_text = array('bit', 'bigint', 'bool', 'boolean',
        'dec', 'decimal', 'double', 'float',
        'int', 'integer', 'mediumint', 'real',
        'smallint', 'tinyint', 'function'
    );

// session chack
    $row_id = FALSE;
    $oSession = Session::get_instance();
    $user_id = $oSession->user->user_id;
    $columns = array();
    $data = array();
    $query_start = '';
    $query_end = '';
    // $query_data  = '';

    $col_result = mysqli_query(DB::$link,  "SHOW COLUMNS FROM $table");
    if ($col_result === FALSE) {
      Corelog::log("DB:unknown table: $table: " . mysqli_error(DB::$link), Corelog::ERROR);
      return FALSE;
    }
    // Field      | Type     | Null | Key | Default | Extra  ==> Value
    while ($column = mysqli_fetch_assoc($col_result)) {
      $column_name = $column['Field'];

      switch ($column_name) {
        case 'date_created':
        case 'last_updated':
          $columns[$column_name] = $column;
          $columns[$column_name]['Value'] = time();
          break;
        case 'updated_by':
          $columns[$column_name] = $column;
          $columns[$column_name]['Value'] = $user_id;
          break;
        case 'created_by':
          $columns[$column_name] = $column;
          $columns[$column_name]['Value'] = $user_id;
          if (!can_access($table . '_admin')) {
            // if no admin then prevent this field from being customized
            break;
          }
        default:

          if (array_key_exists($column_name, $values) && $values[$column_name] !== NULL) {
            //  update <=              or if INSERT then don't include empty values
            if ($primary_key !== FALSE || ($primary_key === FALSE && $values[$column_name] != '')) {
              $columns[$column_name] = $column;
              $columns[$column_name]['Value'] = mysqli_real_escape_string(DB::$link, $values[$column_name]);
            }
          }
          
          break;
      }
    }

   
    if ($primary_key === FALSE) { // new record => INSERT
      // this will fix a bug related to pooerly written loops
      if (isset($values['primary_key']) && $values['primary_key'] == $values[$table . '_id']) {
        unset($values['primary_key']);
        unset($values[$table . '_id']);
      }
      unset($columns['updated_by']);
      unset($columns['last_updated']);

      if ($check_auth && $foreign_table != '') {
        $foreign_value = $columns[$foreign_key]['Value'];
        if (!self::auth_filter($foreign_table, $foreign_key, $foreign_value)) {
          return FALSE;
        }
      }

      $query_start = "INSERT INTO $table SET";
      $query_end = '';
    } else { // existing record => UPDATE
      $row_id = $columns[$primary_key]['Value'];

      /* // remove unchanged columns from query
        $current_rs   = mysqli_query(DB::$link, "SELECT * FROM $table_name WHERE $primary_key = $row_id");
        $current_data = mysqli_fetch_assoc($current_rs);
        foreach ($columns as $col_name => $col_value) {
        if ($col_value == $current_data[$col_name]) {
        unset($columns[$col_name]);
        }
        } */

      unset($columns[$primary_key]);
      unset($columns['created_by']);
      unset($columns['date_created']);

      $query_start = "UPDATE $table SET";

      if ($check_auth) {
        if ($foreign_table == '') { // main table
          $auth_filter = self::auth_filter($table);
        } else { // sub table
          $foreign_value = $columns[$foreign_key]['Value'];
          unset($columns[$foreign_key]);
          $auth_filter = self::auth_filter($foreign_table, $foreign_key, $foreign_value);
        }
        if (is_bool($auth_filter)) {
          $auth_filter = ($auth_filter) ? 'TRUE' : 'FALSE';
        }
        $query_end = "WHERE $primary_key=$row_id AND $auth_filter";
      } else {
        $query_end = "WHERE $primary_key=$row_id";
      }
    }

    foreach ($columns as $key => $column) {
      $value = $column['Value'];
      $type = strtolower(substr($column['Type'], 0, strpos($column['Type'], '(')));
      if (in_array(trim($type), $non_text)) { // number
        if ("$value" === '') { // don't allow empty value as number, instead replace it with NULL
          $data[$key] = "$key=NULL";
        } else {
          $data[$key] = "$key=$value";
        }
      } else {    // string
        $data[$key] = "$key='$value'";
      }
    }
    $query_data = implode(', ', $data);
    $query_full = "$query_start $query_data $query_end";
    $qry_result = mysqli_query(DB::$link, $query_full);
    Corelog::log("DB:update query executed on table: $table", Corelog::DEBUG, $query_full);
    if ($qry_result === FALSE) {
      Corelog::log("DB:update error table: $table error: " . mysqli_error(DB::$link), Corelog::WARNING);
      return FALSE;
    }
    $values['primary_key'] = mysqli_insert_id(DB::$link);
    if ($primary_key === FALSE) {
      $values[$table . '_id'] = $values['primary_key'];
    }
    return $qry_result;
  }
  public static function query_result($table, $query, $field, $aValues = array(), $check_auth = FALSE, $foreign_table = '', $foreign_key = '')
  {
    $result = self::query($table, $query, $aValues , $field, $check_auth, $foreign_table, $foreign_key);
      if ($result instanceof \mysqli_result) {
          while ($row = mysqli_fetch_assoc($result)) {
              if (array_key_exists($field, $row)) {
                  return $row[$field];
              }
          }
      } else {
          // Handle the case where the query result is not a valid mysqli_result
          return null;
      }
      return null;
  }
  static function query($table, $req_query, $aValues = array(), $check_auth = FALSE, $foreign_table = '', $foreign_key = '')
  {
    $values = array();
    foreach ($aValues as $key => $value) {
      $values["%$key%"] = mysqli_real_escape_string(DB::$link, $value);
    }
    if ($check_auth) {
      if ($foreign_table == '') {
        $values['%auth_filter%'] = self::auth_filter($table);
      } else {
        $values['%auth_filter%'] = self::auth_filter($foreign_table, $foreign_key, $values["%$foreign_key%"]);
      }
    } else {
      $values['%auth_filter%'] = TRUE;
    }

    // in case of Boolean following code will remove table prefix if any exist with auth_filter
    if (is_bool($values['%auth_filter%'])) {
      $boolStr = ($values['%auth_filter%']) ? 'TRUE' : 'FALSE';
      $req_query = preg_replace('/(\w*\.)?\%auth_filter\%/', $boolStr, $req_query);
    }
    $final_query = str_replace(array_keys($values), array_values($values), $req_query);
    Corelog::log("DB:query executed on table: $table", Corelog::DEBUG, $final_query);
    $result = mysqli_query(DB::$link, $final_query);
    
    if (!$result) {
        Corelog::log("Error executing query: $final_query", Corelog::ERROR);
        Corelog::log("MySQL Error: " . mysqli_error(DB::$link), Corelog::ERROR);
    }
    
    return $result;

  }
  static function delete($table, $primary_key, $row_id, $check_auth = FALSE, $foreign_table = '', $foreign_key = '', $foreign_value = '')
  {
    $values = array($primary_key => $row_id);
    Corelog::log("DB:delete requested on table: $table", Corelog::DEBUG);
    if ($check_auth) {
      $query = "DELETE FROM $table WHERE $primary_key=%$primary_key% AND %auth_filter%";
      $values[$foreign_key] = $foreign_value;
      return self::query($table, $query, $values, TRUE, $foreign_table, $foreign_key);
    } else {
      $query = "DELETE FROM $table WHERE $primary_key=%$primary_key%";
      return self::query($table, $query, $values, FALSE);
    }
  }
  static function auth_filter($table, $auth_key = 'created_by', $auth_value = FALSE)
  {
    if (can_access($table . '_admin')) {
      return TRUE;
    } else {
      $oSession = Session::get_instance();
      if ($auth_key == 'created_by') { // main table
        $auth_value = $oSession->user->user_id;
        if (empty($auth_value)) {
          return FALSE; // null user id is not allowed
        }
      } else { // sub table
        $user_value = $oSession->user->user_id;
        if (empty($user_value)) {
          return FALSE; // null user id is not allowed
        }
        $user_key = 'created_by';
        $auth_value = mysqli_real_escape_string(DB::$link, $auth_value);
        $parent_query = "SELECT $auth_key FROM $table WHERE $auth_key=$auth_value AND $user_key=$user_value";
        $parent_result = mysqli_query(DB::$link, $parent_query);
        if (mysqli_num_rows($parent_result) == NULL) {
          return FALSE;
        }
      }
      return "$auth_key=$auth_value";
    }
  }

}