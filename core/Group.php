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
      'contact_total',
      'description'
  );
  private static $read_only = array(
      'group_id',
      'contact_total'
  );

  /**
   * @property-read integer $group_id
   * @var integer
   */
  public $group_id = NULL;

  /** @var string */
  public $name = NULL;

  /** @var integer */
  private $contact_total = 0;

  /** @var string */
  public $description = '';

  /**
   * @property-read integer $user_id
   * owner id of current record
   * @var integer
   */
  public $user_id = NULL;

  public function __construct($group_id = NULL)
  {
    if (!empty($group_id) && $group_id > 0) {
      $this->group_id = $group_id;
      $this->load();
    } 
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

        case 'user_id':
        case 'created_by':
          $aWhere[] = "created_by = '$search_value'";
          break;
        case 'before':
          $aWhere[] = "date_created <= $search_value";
          break;
        case 'after':
          $aWhere[] = "date_created >= $search_value";
          break;
      }
    }
    if (!empty($aWhere)) {
      $from_str .= ' WHERE ' . implode(' AND ', $aWhere);
    }

    $query = "SELECT group_id, name, contact_total FROM " . $from_str;
    Corelog::log("group search with $query", Corelog::DEBUG, array('aFilter' => $aFilter));
    $result = DB::query('group', $query);
    forEach ($result as $data ) {
      $aGroup[] = $data;
    }

    return $aGroup;
  }

  // List Group Contact
  public function search_contact($aFilter = array(), $full = false)
  {
    $aFilter['group_id'] = $this->group_id;
    return Contact::search($aFilter, $full);
  }

  private function load()
  {
    $query = "SELECT * FROM " . self::$table . " WHERE group_id='%group_id%' ";
    $result = DB::query(self::$table, $query, array('group_id' => $this->group_id));
    $data = $result[0];
    if ($data) {
      $this->group_id = $data['group_id'];
      $this->name = $data['name'];
      $this->contact_total = $data['contact_total'];
      $this->description = $data['description'];
      $this->user_id = $data['created_by'];
      Corelog::log("group loaded name: $this->name", Corelog::CRUD);
    } else {
      throw new CoreException('404', 'Group not found');
    }
  }

  public function delete()
  {
    Corelog::log("Group delete", Corelog::CRUD);
    return DB::delete(self::$table, 'group_id', $this->group_id);
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

  public function save()
  {
    $data = array(
        'group_id' => $this->group_id,
        'name' => $this->name,
        // read only 'contact_total' => $this->contact_total,
        'description' => $this->description
    );

    if (isset($data['group_id']) && !empty($data['group_id'])) {
      // update existing record
      $result = DB::update(self::$table, $data, 'group_id');
      Corelog::log("group updated: $this->group_id", Corelog::CRUD);
    } else {
      // add new
      $result = DB::update(self::$table, $data, false);
      $this->group_id = $data['contact_group_id']; // NOTE: DB::update using table name suffixed with _id as primary key
      Corelog::log("New group created: $this->group_id", Corelog::CRUD);
    }
    return $result;
  }

    public function get_crm_target_list() {

    $url = Conf::get('crm:url', '');
    if (!empty($url)) {
      $username = Conf::get('crm:username', '');
      $password = Conf::get('crm:password', '');

      $login_parameters = array(
         "user_auth" => array(
              "user_name" => $username,
              "password" => md5($password),
              "version" => "1"
         ),
         "application_name" => "RestTest",
         "name_value_list" => array(),
     );

     $login_result = Group::call("login", $login_parameters, $url);

     $entryArgs = array(
         //Session id - retrieved from login call
	'session' => $login_result->id,
         //Module to get_entry_list for
	'module_name' => 'ProspectLists',
         //Order by - unused
	'order_by' => '',
         'query' => '',
         //Start with the first record
	'offset' => 0,
         //Return the id and name fields
	'select_fields' => array('id','name','first_name','last_name'),   

         //Do not show deleted
  	'deleted' => 0,
    );

    $list_result =Group::call('get_entry_list', $entryArgs, $url);
    $result = array();

    foreach($list_result->entry_list as $entry) {
      $object = new \stdClass(); 
      $object->name = $entry->name_value_list->name->value;
      $object->group_id = $entry->name_value_list->id->value;

      array_push($result, $object);
    }
    return $result; 
    }
    else {
      throw new CoreException(411, "CRM Not configured");
    }

  }

  public static function call($method, $parameters, $url)
    {
        ob_start();
        $curl_request = curl_init();

        curl_setopt($curl_request, CURLOPT_URL, $url);
        curl_setopt($curl_request, CURLOPT_POST, 1);
        curl_setopt($curl_request, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($curl_request, CURLOPT_HEADER, 1);
        curl_setopt($curl_request, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl_request, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_request, CURLOPT_FOLLOWLOCATION, 0);

        $jsonEncodedData = json_encode($parameters);

        $post = array(
             "method" => $method,
             "input_type" => "JSON",
             "response_type" => "JSON",
             "rest_data" => $jsonEncodedData
        );

        curl_setopt($curl_request, CURLOPT_POSTFIELDS, $post);
        $result = curl_exec($curl_request);
        curl_close($curl_request);

        $result = explode("\r\n\r\n", $result, 2);
        $response = json_decode($result[1]);
        ob_end_flush();

        return $response;
    }
  
}
