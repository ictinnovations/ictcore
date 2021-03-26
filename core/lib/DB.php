<?php

namespace ICT\Core;

/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

// DB related functions
use ICT\Core\CoreException;
use PDO;

class DB
{
    static $link = NULL;
    static $pdo = NULL;

    public function __construct()
    {
        $db_port = Conf::get('db:port', '3306');
        $db_host = Conf::get('db:host', 'localhost') . ':' . $db_port;
        $db_user = Conf::get('db:user', 'myuser');
        $db_pass = Conf::get('db:pass', '');
        $db_name = Conf::get('db:name', 'ictcore');

        try {
            self::$pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (Exception $e) {
            echo "Connection failed" . $e->getMessage();
        }

        //$dsn = $this->_engine.':dbname='.$this->_db.';host='.$this->_server.';charset=utf8';
        //parent::__construct($dsn, $this->_user, $this->_password);
        //$this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    }

    static function connect($link_new = FALSE)
    {
        $db_port = Conf::get('db:port', '3306');
        $db_host = Conf::get('db:host', 'localhost') . ':' . $db_port;
        $db_user = Conf::get('db:user', 'myuser');
        $db_pass = Conf::get('db:pass', '');
        $db_name = Conf::get('db:name', 'ictcore');

        try {
            self::$pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (Exception $e) {
            echo "Connection failed" . $e->getMessage();
        }
        //mysqli_query("SET time_zone = '+00:00'", $link); // required to bypass server timezone settings

        return self::$pdo;
    }

    static function next_record_id($table, $field = '')
    {
        //replacing: $result = mysql_query("SELECT sequence FROM sequence WHERE table_name='$table'", DB::$link);
        $stmt = self::$pdo->prepare("SELECT sequence FROM sequence WHERE table_name=:table");
        $stmt->execute(['table' => $table]);
        $result = $stmt->fetchAll();
        if ($result) {
            $newid = count($result) + 1;
            //replacing:mysql_query("UPDATE sequence SET sequence = $newid WHERE table_name = '$table'", DB::$link);
            $stmt = self::$pdo->prepare("UPDATE sequence SET sequence = :newid WHERE table_name = :table");
            $stmt->execute(['table' => $table, 'newid' => $newid]);
            return $newid;
        } else {
            $field = $field ? $field : $table . '_id';
            //$result = mysql_query("SELECT MAX($field) as newid FROM $table", DB::$link);
            //searching maximum value in _id column that is the last id
            $stmt = self::$pdo->prepare("SELECT MAX($field) as newid FROM $table");
            $stmt->execute();
            $result = $stmt->fetch();
            if ($result) {
                //Replacing: $col_newid = $result[0];
                $col_newid = $result[0];
                $newid = $col_newid ? $col_newid + 1 : 1;
            } else {
                $newid = 0;
            }
            $newid = $newid ? $newid + 1 : 1;

            //mysql_query("INSERT INTO sequence (table_name, sequence) VALUES ('$table', $newid)", DB::$link);

            $stmt = self::$pdo->prepare("INSERT INTO sequence (table_name, sequence) VALUES (:table, :newid)");
            $stmt->execute(['table_name' => $table, 'newid' => $newid]);

            return $newid;
        }
    }

    static function column_list($table)
    {
        $aColumn = array();
        //$result = mysql_query("SHOW COLUMNS FROM $table", DB::$link);
        try {
            $stmt = self::$pdo->query("SHOW COLUMNS FROM $table ");
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!$result) {
                return FALSE;
            }
            foreach ($result as $column) {
                $column_name = $column['Field'];
                $aColumn[$column_name] = $column;
            }
        } catch (\Exception $exception) {
            echo $exception->getMessage();
            Corelog::log("DB:unknown table: $table: " . $exception->getMessage(), Corelog::ERROR);
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
                    'value' => $aData[$column_name],
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

        $row_id = FALSE;
        $oSession = Session::get_instance();
        $user_id = $oSession->user->user_id;
        $columns = array();
        $data = array();

        // $col_result = mysql_query("SHOW COLUMNS FROM $table", DB::$link);
        try {
            $stmt = self::$pdo->query("SHOW COLUMNS FROM $table ");
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!$result) {
                return FALSE;
            }


            // Field      | Type     | Null | Key | Default | Extra  ==> Value
            foreach ($result as $column) {
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
                                $columns[$column_name]['Value'] = $values[$column_name];
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
                  $current_rs   = mysql_query("SELECT * FROM $table_name WHERE $primary_key = $row_id", DB::$link);
                  $current_data = mysql_fetch_assoc($current_rs);
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
            $query_data = implode($data, ', ');
            $query_full = "$query_start $query_data $query_end";
            try {
                $qry_result = self::$pdo->query($query_full)->execute();
                Corelog::log("DB:update query executed on table: $table", Corelog::DEBUG, $query_full);
                $values['primary_key'] =  self::$pdo->lastInsertId();
                if ($primary_key === FALSE) {
                    $values[$table . '_id'] = $values['primary_key'];
                }
                return $qry_result;
            } catch (\Exception $exception) {
                Corelog::log("DB:update error table: $table error: " . $exception->getMessage(), Corelog::WARNING);
                return FALSE;
            }


        } catch (\Exception $exception) {
            Corelog::log("DB:unknown table: $table: " . $exception->getMessage(), Corelog::ERROR);
            return FALSE;
        }


    }

    public static function query_result($table, $query)
    {
        try {
            $stmt = self::$pdo->query($query);
            $stmt->execute();
            $result = $stmt->fetchColumn();
            return $result;
        } catch (\Exception $exception) {
            echo $exception->getMessage();
        }
    }

    static function query($table, $req_query, $aValues = array(), $check_auth = FALSE, $foreign_table = '', $foreign_key = '')
    {
       // echo PHP_EOL;
        //echo $req_query." ";
        //echo PHP_EOL;
       // print_r($aValues);
        //echo PHP_EOL;

        $values = array();
        foreach ($aValues as $key => $value) {
            $values["%$key%"] = $value;
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

        //echo PHP_EOL.$final_query . PHP_EOL;

        //return mysql_query($final_query, DB::$link);
        try {
            $stmt = self::$pdo->query($final_query);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (\Exception $exception) {
            echo $exception->getMessage();
        }


    }


    static function delete($table, $primary_key, $row_id, $check_auth = FALSE, $foreign_table = '', $foreign_key = '', $foreign_value = '')
    {
        $values = array($primary_key => $row_id);
        Corelog::log("DB:delete requested on table: $table", Corelog::DEBUG);

        if ($check_auth) {
            $query = "DELETE FROM $table WHERE $primary_key=%$primary_key% AND %auth_filter%";
            $values[$foreign_key] = $foreign_value;
            return self::raw_insert_delete_update($table, $query, $values, TRUE, $foreign_table, $foreign_key);
        } else {
            $query = "DELETE FROM $table WHERE $primary_key=%$primary_key%";
            return self::raw_insert_delete_update($table, $query, $values, FALSE);
        }
    }

    static function auth_filter($table, $auth_key = 'created_by', $auth_value = FALSE)
    {
        if (can_access($table . '_admin')) {
            echo " ADmin access ";
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
                //Not required in pdo
                //$auth_value = mysql_real_escape_string($auth_value, DB::$link);
                $parent_query = "SELECT $auth_key FROM $table WHERE $auth_key=$auth_value AND $user_key=$user_value";

                self::rawSelect($parent_query);
            }

            echo " Auth key vlaue:";
            echo "$auth_key=$auth_value  ";

            return "$auth_key=$auth_value";
        }
    }

    public static function rawSelect($query)
    {
        try {
            $stmt = self::$pdo->query($query);
            $stmt->execute();
            $result = $stmt->fetchAll();
            return $result;
        } catch (\Exception $exception) {
            echo $exception->getMessage();
            return FALSE;
        }

    }

    public static function raw_insert_delete_update($query)
    {
        try {
            $stmt = self::$pdo->query($query);
            $stmt->execute();
           return $stmt->rowCount();
        } catch (\Exception $exception) {
            echo $exception->getMessage();
            return 0;
        }
    }


    static function insert_delete_update($table, $req_query, $aValues = array(), $check_auth = FALSE, $foreign_table = '', $foreign_key = '')
    {
        $values = array();
        foreach ($aValues as $key => $value) {
            $values["%$key%"] = $value;
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

        try {
            $stmt = self::$pdo->query($final_query);
            $stmt->execute();
            return $stmt->rowCount();
        } catch (\Exception $exception) {
            echo $exception->getMessage();
        }

    }




}
