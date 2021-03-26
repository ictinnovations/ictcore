<?php

namespace ICT\Core;

/* * ****************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * **************************************************************** */

/* * ****************************************************************
 *                         USAGE EXAMPLE                           *
 * ******************************************************************

  1. Create / update configuration file
  -------------------------------------
  1. $oConfig = new IB_Config($file_name, $file_path, $gateway_flag);
  2. $config_id = $oConfig->create(); // not required in case of existing record
  3. $oConfig->delete(); // clean any existing data
  4. $oConfig->reference($group_name, $group_child, $description);
  5. $oConfig->insert('for each row of file');
  6. $version = $oConfig->update();

  2. Create / update data/binary file
  -----------------------------------
  1. $oConfig = new IB_Config($file_name, $file_path);
  2. $config_id = $oConfig->create('http://path/to/source/file.ext');
  3. $version = $oConfig->update();

  3. Delete / Drop configuration file
  -----------------------------------
  1. $oConfig = new IB_Config($file_name, $file_path, $gateway_flag);
  2. $oConfig->drop();

  4. Delete a part from configuration file
  ----------------------------------------
  1. $oConfig = new IB_Config($file_name, $file_path, $gateway_flag);
  2. $oConfig->delete($group_name, $group_child);

  5. Save a single file on current node
  -------------------------------------
  1. $oConfig = new IB_Config($file_name, $file_path, $gateway_flag);
  2. $oConfig->load();
  3. $oConfig->save();
  4. $oConfig->update_ack($node_id, $version);

  6. Update all configuration files for a specific node (remote nodes are not supported)
  --------------------------------------------------------------------------------------
  1. $oConfig = new IB_Config();
  2. $oConfig->node_update($node_id, $gateway_flag);

  7. Delete obsolete old data
  ---------------------------
  1. $oConfig = new IB_Config();
  2. $oConfig->clean();

 * * */

class IB_Config
{

  public $config_id = null;
  public $version = null;
  public $is_ini = false;
  public $file_name = '';
  public $file_path = '/usr/ictbroadcast/etc';
  public $gateway_flag = 0;
  public $source = '';
  public $group_name = '';
  public $group_child = '';
  public $node_id = 0;
  public $description = '';
  public $data = array();

  public function __construct($file_name = '', $file_path = '', $gateway_flag = 0)
  {
    global $path_etc;

    $this->gateway_flag = $gateway_flag;
    $this->file_name = $file_name;
    if (empty($file_path)) {
      // no sub-directory
      $this->file_path = $path_etc;
    } else if ($file_path[0] == DIRECTORY_SEPARATOR) {
      // absolute path
      $this->file_path = $file_path;
    } else {
      // sub-directory
      $this->file_path = $path_etc . DIRECTORY_SEPARATOR . $file_path;
    }

    $result = DB::query('config', "SELECT config_id FROM config WHERE file_name='$this->file_name' AND file_path='$this->file_path'");
    if ($result) {
      $this->config_id = $result[0][0];
    } else {
      Corelog::log("Creating new Config $this->file_name", Corelog::COMMON);
    }
  }

  public function create($source = '', $is_ini = false)
  {
    $this->source = $source;
    $this->is_ini = $is_ini;

    if (!$this->config_id) {
      // insert new only if not already exist
      $data = array(
          'file_name' => $this->file_name,
          'file_path' => $this->file_path,
          'source' => $this->source,
          'version' => 1,
          'gateway_flag' => $this->gateway_flag
      );
        $this->config_id = DB::update('config', $data);
    }

    Corelog::log("Config file created $this->file_name", Corelog::COMMON, $this);
    return $this->config_id;
  }

  public function drop()
  {
    Corelog::log("Config file dropped $this->file_name", Corelog::COMMON);
    // set version to -1 to indicate deleted file, configuration daemon will delete on update
    $query = "UPDATE config SET version=-1
              WHERE file_name='$this->file_name' AND file_path='$this->file_path'
              LIMIT 1";
    return DB::query('config', $query);
  }

  public function load($group_name = false, $group_child = false, $description = false, $node_id = false)
  {
    $config = DB::query('config', "SELECT * FROM config WHERE config_id=$this->config_id LIMIT 1");
    if ($config) {
      $this->config_id = $config->config_id;
      $this->source = $config->source;
      $this->version = $config->version;
      $this->file_name = $config->file_name;
      $this->file_path = $config->file_path;
      $this->gateway_flag = $config->gateway_flag;
      $this->data = array();
    }
    $group_filter = '';
    if ($group_name !== false) {
      $group_filter .= " AND group_name='$group_name'";
    }
    if ($group_child !== false) {
      $group_filter .= " AND group_child='$group_name'";
    }
    if ($description !== false) {
      $group_filter .= " AND description='$description'";
    }
    if ($node_id !== false && !empty($node_id)) {
      $group_filter .= " AND (node_id=$node_id OR node_id=0)";
    }
    $query = "SELECT group_name, group_child, data, description
               FROM config_data
               WHERE file_name='$this->file_name' $group_filter
               ORDER BY group_name, group_child, config_data_id";
    $rsData = DB::query('config_data', $query);
    forEach ($rsData as $data_row) {
      $this->data[] = $data_row['data'];
    }
  }

  public function reference($group_name = false, $group_child = false, $description = false, $node_id = false, $reset = false)
  {
    if ($reset) {
      $this->group_name = '';
      $this->group_child = '';
      $this->description = '';
      $this->node_id = 0;
    }
    if ($group_name !== false) {
      $this->group_name = $group_name;
    }
    if ($group_child !== false) {
      $this->group_child = $group_child;
    }
    if ($description !== false) {
      $this->description = $description;
    }
    if ($node_id !== false) {
      $this->node_id = $node_id;
    }
  }

  public function insert($raw_data, $raw_description = false, $skip_duplicate = false)
  {
    if ($raw_description === false) {
      $raw_description = $this->description;
    }

    $description = $raw_description;
    $data = $raw_data;

    if ($skip_duplicate) {
      $query = "SELECT COUNT(*) FROM config_data 
                WHERE group_name='$this->group_name' AND group_child='$this->group_child' AND data='$data' 
                  AND description='$description' AND file_name='$this->file_name'";
      $rsQry = DB::rawSelect($query);
      if ($rsQry[0][0] > 0) {
        return false;
      }
    }

    $query = "INSERT INTO config_data (data, description, group_name, file_name, node_id, gateway_flag, group_child)
              VALUES ('$data', '$description', '$this->group_name', '$this->file_name', 
                       $this->node_id, $this->gateway_flag, '$this->group_child')";
    return DB::query('config_data', $query);
  }

  public function delete($group_name = false, $group_child = false, $description = false, $node_id = false)
  {
    $group_filter = '';
    if ($group_name !== false) {
      $group_filter .= " AND group_name='$group_name'";
    }
    if ($group_child !== false) {
      $group_filter .= " AND group_child='$group_name'";
    }
    if ($description !== false) {
      $group_filter .= " AND description='$description'";
    }
    if ($node_id !== false && !empty($node_id)) {
      $group_filter .= " AND node_id='$node_id'";
    }
    $query = "DELETE FROM config_data
              WHERE file_name='$this->file_name' $group_filter";
    return DB::query('config_data', $query);
  }

  public function update()
  {
    // only update when file fully ready as it may trigger file update on nodes
    // currently not fully implemented
    DB::query('config', "UPDATE config SET version=version+1 WHERE file_name='$this->file_name' LIMIT 1");
    $result = DB::query('config', "SELECT version FROM config WHERE file_name='$this->file_name' LIMIT 1");
    $this->version = $result[0];
    return $this->version;
  }

  public function save()
  {
    Corelog::log("Saving Config file $this->file_name", Corelog::COMMON);
    $full_path = $this->file_path . DIRECTORY_SEPARATOR . $this->file_name;
    if (empty($this->source)) {
      $data = implode("\n", $this->data);
      file_put_contents($full_path, $data);
    } else { // else $config->source is set
      // download from remote $config->source location into temp file
      $ft = tempnam("/tmp", "ib_download");
      $ch = curl_init($this->source);
      $fp = fopen($ft, 'wb');
      curl_setopt($ch, CURLOPT_FILE, $fp);
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_exec($ch);
      $rs = curl_getinfo($ch);
      curl_close($ch);
      fclose($fp);

      if ($rs['http_code'] == '200' && $rs['content_type'] != 'text/html; charset=UTF-8') {
        copy($ft, $full_path);
      } else {
        return false;
      }
    }
    return true;
  }

  public function node_update($node_id, $gateway_flag = false)
  {
    Corelog::log("Updating node Configs, node_id: $node_id gateway_flag: $gateway_flag", Corelog::COMMON);

    $is_changed = false;
    $query_filter = '';
    if ($gateway_flag) {
      $query_filter .= "AND ($gateway_flag & c.gateway_flag) = c.gateway_flag";
    }
    $query = "SELECT c.* FROM config c 
                LEFT JOIN 
                  (SELECT * FROM config_node WHERE node_id = $node_id) cn 
                ON c.config_id = cn.config_id
              WHERE c.version!=0 
                AND (cn.version != c.version || cn.config_id IS NULL)
                $query_filter";
    $rs1 = DB::query('config', $query);

    forEach ($rs1 as $config) {
      $this->config_id = $config->config_id;
      $this->load(false, false, false, $node_id);

      if ($config->version > 0) {
        if ($this->save()) {
          $this->node_update_ack($node_id);
        }
      } else if ($config->version < 0) { // if version is negative then delete that file
        if (!empty($config->file_name)) { // check filename to avoid directory deletion
          $full_path = $config->file_path . DIRECTORY_SEPARATOR . $config->file_name;
          exec("rm -rf '$full_path'");
        }
        $this->node_update_ack($node_id);
      } // do nothing if version == 0

      $is_changed = true;
    }
    return $is_changed;
  }

  public function node_update_ack($node_id)
  {
    // insert new record in config_node table if there is not already
    $result = DB::query('config_node', "SELECT * FROM config_node WHERE config_id=$this->config_id AND node_id=$node_id LIMIT 1");
    if (count($result) < 1) {
      DB::query('config_node', "INSERT INTO config_node (config_id, node_id, date_created) 
                               VALUES ($this->config_id, $node_id, UNIX_TIMESTAMP())");
    }
    // update node file record in db with recent version of file
    return DB::query('config_node', "UPDATE config_node SET version='$this->version', last_updated=UNIX_TIMESTAMP() 
                                    WHERE config_id=$this->config_id AND node_id=$node_id");
  }

  public function clean()
  {
    Corelog::log("Cleaning absolute Config files", Corelog::COMMON);
    $result = DB::query('config', "SELECT config_id FROM config WHERE version < 0");
    if($result){
    forEach ($result as $config) {
      $rsData = DB::query('config_data', "SELECT * FROM config_node WHERE file_name='$config->file_name' AND version > 0");
      //:confusion
      if (!count($rsData)) {
        DB::query('config_data', "DELETE FROM config_node WHERE file_name='$config->file_name'");
        DB::query('config', "DELETE FROM config WHERE config_id=$config->config_id");
      }
    }
    }
  }

}