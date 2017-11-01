<?php
namespace ICT\Core;
/* * ***************************************************************
 * Copyright © 2017 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */
class Group
{
  /** @const */
  const COMPANY = -2;
  private static $table = 'contact_group';
  private static $primary_key = 'group_id';
  private static $fields = array(
      'group_id',
      'name',
      'description'
  );
  private static $read_only = array(
      'group_id'
  );
  /**
   * @property-read integer $group_id
   * @var integer
   */
  private $group_id = NULL;
  /** @var string */
  public $name = NULL;
  /** @var string */
  public $description = '';
  public function __construct($group_id = NULL)
  {
    if (!empty($group_id) && $group_id > 0) {
      $this->group_id = $group_id;
      $this->load();
    } 
  }
  public static function construct_from_array($aGroup)
  {
    $ogroup = new group();
    foreach ($aGroup as $field => $value) {
    $ogroup->$field = $value;
    }
    return $ogroup;
  }
  public static function search($aFilter = array())
  {
    $aGroup = array();
    $from_str = self::$table;
    $aWhere = array();
    foreach ($aFilter as $search_field => $search_value) {
      switch ($search_field) {
        case 'group_id':
          $aWhere[] = "$search_field = $search_value";
          break;
        case 'name':
          $aWhere[] = "$search_field LIKE '%$search_value%'";
          break;
      }
    }
    if (!empty($aWhere)) {
      $from_str .= ' WHERE ' . implode(' AND ', $aWhere);
    }
    $query = "SELECT group_id, name FROM " . $from_str;
    Corelog::log("group search with $query", Corelog::DEBUG, array('aFilter' => $aFilter));
    $result = DB::query('group', $query);
    while ($data = mysql_fetch_assoc($result)) {
      $aGroup[] = $data;
    }
    // if no group found, check for special groups
    /*if (empty($aGroup) && isset($aFilter['group_id']) && $aFilter['group_id'] == Group::COMPANY) {
      $ogroup = new Group($aFilter['group_id']);
      $aGroup[$ogroup->group_id] = array(
          'group_id' => $ogroup->group_id,
          'name' => $ogroup->name,
      );
    }*/
    return $aGroup;
  }
  //List Group Contact
public function search_contact()
{
    $aGroupcontact = array();
    $aWhere = array();
   //$from_str .= ' WHERE cl.group_id='.$this->group_id;
    $query = "SELECT c.first_name,c.last_name,c.phone,c.email,c.contact_id ,cl.contact_id ,cl.group_id FROM contact c INNER JOIN contact_link cl ON c.contact_id = cl.contact_id where cl.group_id=".$this->group_id." GROUP BY cl.contact_id";
    Corelog::log("groupcontact search with $query", Corelog::DEBUG, array('aFilter' => $aFilter));
    $result = mysql_query($query);
    while ($data = mysql_fetch_assoc($result)) {
      $aGroupcontact[] = $data;
    }
    return $aGroupcontact;
}
  // END
  //export csv
  public function contact_export()
  {
     $tmpfname = tempnam("", "contact_");
     //$output = fopen($tmpfname.".csv", "w");  
     $output = fopen('php://output', 'w');
    fputcsv($output, array('First Name', 'Last Name', 'Phone','Email'));  
    $query = "SELECT c.first_name,c.last_name,c.phone,c.email,c.contact_id ,cl.contact_id ,cl.group_id FROM contact c INNER JOIN contact_link cl ON c.contact_id = cl.contact_id where cl.group_id=".$this->group_id." GROUP BY cl.contact_id";
    $result = mysql_query($query);  
    while($row = mysql_fetch_assoc($result))  
    {  
      unset($row['contact_id']);
      unset($row['group_id']);
      fputcsv($output, $row);  

    }  
    return $tmpfname;
  }
   //end
  // import csv using Deamon
    public function contact_import($file)
    {
       //exec(dirname(__DIR__)."/bin/contact.php hello start" , $output);
        $contact_imp  = $file['file_contents']['tmp_name'];
        exec(dirname(__DIR__)."/bin/contact.php $contact_imp $this->group_id start ", $output);
        return $output[0] ;
       /* $file_tmpname = fopen($file['file_contents']['tmp_name'], "r");
        $i=0;
        while (($value = fgetcsv($file_tmpname, 10000, ",")) !== FALSE)
        {
          if(!empty($value))
          {
            //echo $getData[0];
            $result_add = mysql_query("INSERT INTO contact(first_name,last_name,phone,email,address,custom1,custom2,custom3,description) value ('".$value[1]."','".$value[2]."','".$value[0]."','".$value[3]."','".$value[4]."','".$value[5]."','".$value[6]."','".$value[7]."','".$value[8]."')");
            $result = mysql_insert_id();
            $result_add = mysql_query("INSERT INTO contact_link(group_id,contact_id) value (".$this->group_id.",$result )");
          }
           $i++;
        }*/
        /*foreach ($data as $key => $value) 
        {
         // echo "Select * from contact where phone=".$value[0]." AND email ='".$value[3]."'";
            $check_pone_email= mysql_query("Select * from contact where phone=".$value[0]." AND email ='".$value[3]."'");

            if(mysql_num_rows($check_pone_email)== 0)
            {
                $result_add = mysql_query("INSERT INTO contact(first_name,last_name,phone,email,address,custom1,custom2,custom3,description) value ('".$value[1]."','".$value[2]."','".$value[0]."','".$value[3]."','".$value[4]."','".$value[5]."','".$value[6]."','".$value[7]."','".$value[8]."')");
                $result = mysql_insert_id();
                $result_add = mysql_query("INSERT INTO contact_link(group_id,contact_id) value ($id,$result )");
                $i++;
            }
        }*/
       //updade count contact group wise
    // $count_contact = mysql_query("SELECT * from contact_link where group_id=".$id." GROUP BY contact_id");
     //$cont_result =  mysql_num_rows($count_contact);
    // $udate_group = mysql_query("UPDATE contact_group set contact_count=".$cont_result." where group_id=".$id);
     /*$reslt = " Rows Added";
     return $i.$reslt ;*/
  }
  // END
  private function load()
  {
    $query = "SELECT * FROM " . self::$table . " WHERE group_id='%group_id%' ";
    $result = DB::query(self::$table, $query, array('group_id' => $this->group_id), true);
    $data = mysql_fetch_assoc($result);
      if ($data) 
      {
        $this->group_id = $data['group_id'];
        $this->name = $data['name'];
        $this->description = $data['description'];
        Corelog::log("group loaded name: $this->name", Corelog::CRUD);
      } else 
      {
        throw new CoreException('404', 'Group not found');
      }
  }
  public function delete()
  {
    Corelog::log("Group delete", Corelog::CRUD);
    return DB::delete(self::$table, 'group_id', $this->group_id, true);
  }
  public function __isset($field)
  {
    $method_name = 'isset_' . $field;
    if (method_exists($this, $method_name)) {
      return $this->$method_name();
    } else {
      return isset($this->$field);
    }
  }
  public function __get($field)
  {
    $method_name = 'get_' . $field;
    if (method_exists($this, $method_name)) {
      return $this->$method_name();
    } else if (!empty($field) && isset($this->$field)) {
      return $this->$field;
    }
    return NULL;
  }
  public function __set($field, $value)
  {
    $method_name = 'set_' . $field;
    if (method_exists($this, $method_name)) {
      $this->$method_name($value);
    } else if (empty($field) || in_array($field, self::$read_only)) {
      return;
    } else {
      $this->$field = $value;
    }
  }
  public function get_id()
  {
    return $this->group_id;
  }
  public function sample_link()
  {
    $filename = __DIR__.'/../data/contact.csv';
    readfile($filename);
  }
  public function save()
  {
    $data = array(
        'group_id' => $this->group_id,
        'name' => $this->name,
        'description' => $this->description
    );
    if (isset($data['group_id']) && !empty($data['group_id'])) {
      // update existing record
      $result = DB::update(self::$table, $data, 'group_id', true);
      Corelog::log("group updated: $this->group_id", Corelog::CRUD);
    }else{
      // add new
      $result = DB::update(self::$table, $data, false, true);
      $this->group_id = $data['group_id'];
      Corelog::log("New group created: $this->group_id", Corelog::CRUD);
    }
    return $result;
  }
}
