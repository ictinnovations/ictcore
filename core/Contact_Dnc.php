<?php
namespace ICT\Core;
/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */
class Contact_Dnc
{
  /** @const */
  const COMPANY = -2;
  private static $table = 'contact_dnc';
  private static $table_link = 'contact_link';
  private static $primary_key = 'contact_dnc_id';
  private static $fields = array(
      'contact_dnc_id',
      'first_name',
      'last_name',
      'phone',
      'email',
      'address',
      'custom1',
      'custom2',
      'custom3',
      'description'
  );
  private static $read_only = array(
      'contact_dnc_id'
  );

  /**
   * @property-read integer $contact_dnc_id
   * @var integer
   */
  public $contact_dnc_id = NULL;

  /** @var string */
  public $first_name = NULL;

  /** @var string */
  public $last_name = NULL;

  /** @var string */
  public $phone = NULL;

  /** @var string */
  public $email = NULL;

  /** @var string */
  public $address = NULL;

  /** @var string */
  public $custom1 = NULL;

  /** @var string */
  public $custom2 = NULL;

  /** @var string */
  public $custom3 = NULL;

  /** @var string */
  public $description = '';

  /**
   * @property-read integer $user_id
   * owner id of current record
   * @var integer
   */
  public $user_id = NULL;


  public function __construct($contact_dnc_id = NULL)
  {
    if (!empty($contact_dnc_id) && $contact_dnc_id > 0) {
      $this->contact_dnc_id = $contact_dnc_id;
      $this->load();
    } else if (Contact_Dnc::COMPANY == $contact_dnc_id) {
      $this->contact_dnc_id = $contact_dnc_id;
      $title = Conf::get('company:title', 'ICTCore');
      $aTitle = explode(' ', $title, 2);
      $this->first_name = $aTitle[0];
      $this->last_name = isset($aTitle[1]) ? $aTitle[1] : '';
      $this->email = Conf::get('company:email', 'no-reply@example.com');
      $this->phone = Conf::get('company:phone', '1111111111');
      $this->address = Conf::get('company:address', 'PK');
    }
  }


  public static function construct_from_array($aContact_Dnc)
  {
    $oContact_Dnc = new Contact_Dnc();
    foreach ($aContact_Dnc as $field => $value) {
      $oContact_Dnc->$field = $value;
    }
    return $oContact_Dnc;
  }


  public static function locate($Contact_Dnc, $Contact_DncField = 'phone')
  {
    // locate an existing Contact_Dnc or create it
    $Contact_DncFilter = array($Contact_DncField => $Contact_Dnc);
    $listContact_Dnc = Contact_Dnc::search($Contact_DncFilter);
    if ($listContact_Dnc) {
      $aContact_Dnc = array_shift($listContact_Dnc);
      $oContact_Dnc = new Contact_Dnc($aContact_Dnc['contact_dnc_id']);
      return $oContact_Dnc;
    }
    return false;
  }


  public static function search($aFilter = array(), $full = false)
  {
    $aContact_Dnc = array();
    $from_str = self::$table;
    $aWhere = array();
    foreach ($aFilter as $search_field => $search_value) {
      switch ($search_field) {
        case 'contact_dnc_id':
          $aWhere[] = "$search_field = $search_value";
          break;
        case 'phone':
          $aWhere[] = "$search_field LIKE '%$search_value'";
          break;
        case 'email':
          $aWhere[] = "$search_field = '$search_value'";
          break;
        case 'first_name':
        case 'last_name':
          $aWhere[] = "$search_field LIKE '%$search_value%'";
          break;
        case 'user_id':
        case 'created_by':
          $aWhere[] = "created_by = '$search_value'";
          break;
        case 'before':
          $aWhere[] = "date_created <= '$search_value'";
          break;
        case 'after':
          $aWhere[] = "date_created >= '$search_value'";
          break;
      }
    }
    if (!empty($aWhere)) {
      $from_str .= ' WHERE ' . implode(' AND ', $aWhere);
    }
    if ($full) {
      $query = "SELECT contact_dnc_id, first_name, last_name, phone, email, address, "
              ."custom1, custom2, custom3, description FROM " . $from_str;
            } else {
              $query = "SELECT contact_dnc_id, first_name, last_name, phone, email FROM " . $from_str;
            }
            Corelog::log("Contact_Dnc search with $query", Corelog::DEBUG, array('aFilter' => $aFilter));
            $result = DB::query('Contact_Dnc', $query);
            while ($data = mysqli_fetch_assoc($result)) {
              $aContact_Dnc[] = $data;
            }
    // if no Contact_Dnc found, check for special contacts
    if (empty($aContact_Dnc) && isset($aFilter['contact_dnc_id']) && $aFilter['contact_dnc_id'] == Contact_Dnc::COMPANY) {
      $oContact_Dnc = new Contact_Dnc($aFilter['contact_dnc_id']);
      $singleContact_Dnc['contact_dnc_id'] = $oContact_Dnc->contact_dnc_id;
      $singleContact_Dnc['first_name'] = $oContact_Dnc->first_name;
      $singleContact_Dnc['last_name'] = $oContact_Dnc->last_name;
      $singleContact_Dnc['phone'] = $oContact_Dnc->phone;
      $singleContact_Dnc['email'] = $oContact_Dnc->email;
      if ($full) {
        $singleContact_Dnc['address'] = '';
        $singleContact_Dnc['custom1'] = '';
        $singleContact_Dnc['custom2'] = '';
        $singleContact_Dnc['custom3'] = '';
        $singleContact_Dnc['descritpion'] = '';
      }
      $aContact_Dnc = $singleContact_Dnc;
    }
    return $aContact_Dnc;
  }


  private function load()
  {
    $query = "SELECT * FROM " . self::$table . " WHERE contact_dnc_id='%contact_dnc_id%' ";
    $result = DB::query(self::$table, $query, array('contact_dnc_id' => $this->contact_dnc_id));
    $data = mysqli_fetch_assoc($result);
    if ($data) {
      $this->contact_dnc_id = $data['contact_dnc_id'];
      $this->first_name = $data['first_name'];
      $this->last_name = $data['last_name'];
      $this->phone = $data['phone'];
      $this->email = $data['email'];
      $this->address = $data['address'];
      $this->custom1 = $data['custom1'];
      $this->custom2 = $data['custom2'];
      $this->custom3 = $data['custom3'];
      $this->description = $data['description'];
      $this->user_id = $data['created_by'];
      Corelog::log("Contact_Dnc loaded name: $this->first_name $this->last_name", Corelog::CRUD);
    } else {
      throw new CoreException('404', 'Contact_Dnc not found');
    }
  }


  public function delete()
  {
    Corelog::log("Contact_Dnc delete", Corelog::CRUD);
    mysqli_query("DELETE from contact_link where contact_dnc_id=".$this->contact_dnc_id);
    DB::delete(self::$table_link, 'contact_dnc_id', $this->contact_dnc_id);
    return DB::delete(self::$table, 'contact_dnc_id', $this->contact_dnc_id);
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
    return $this->contact_dnc_id;
  }


  public function email_to_phone()
  {
    $aEmail = imap_rfc822_parse_adrlist($this->email, Conf::get('sendmail:domain', 'localhost'));
    $strPhone = $aEmail[0]->mailbox; // we are only interested in 1st (0) part of aEmail list
    $this->phone = preg_replace("/[^0-9]/", "", $strPhone); // keep only digits
    return $this->phone;
  }


  public function phone_to_email()
  {
    $strEmail = $this->phone . '@' . Conf::get('sendmail:domain', 'localhost');
    $this->email = $strEmail;
    return $this->email;
  }


  public function save()
  {
    $data = array(
        'contact_dnc_id' => $this->contact_dnc_id,
        'first_name' => $this->first_name,
        'last_name' => $this->last_name,
        'phone' => $this->phone,
        'email' => $this->email,
        'address' => $this->address,
        'custom1' => $this->custom1,
        'custom2' => $this->custom2,
        'custom3' => $this->custom3,
        'description' => $this->description
    );
    if (isset($data['contact_dnc_id']) && !empty($data['contact_dnc_id'])) {
      // update existing record
      $result = DB::update(self::$table, $data, 'contact_dnc_id');
      Corelog::log("Contact updated: $this->contact_dnc_id", Corelog::CRUD);
    } else {
      // add new
      $result = DB::update(self::$table, $data, false);
      $this->contact_dnc_id = $data['contact_dnc_id'];
      Corelog::log("New Contact created: $this->first_name", Corelog::FLOW);
    }
    return $result;
  }


  public function link($group_id)
  {
    // add new
    $link = array(
        'contact_dnc_id' => $this->contact_dnc_id,
        'group_id' => $group_id
    );
    return DB::update(self::$table_link, $link);
  }


  public function link_delete($group_id = null)
  {
    if ($group_id == null) {
      $link_delete_query = "DELECT FROM ".self::$table_link." WHERE contact_dnc_id=%contact_dnc_id%";
    } else {
      $link_delete_query = "DELETE FROM ".self::$table_link." WHERE contact_dnc_id=%contact_dnc_id% AND group_id=%group_id%";
    }
    DB::query(self::$table, $req_query, array('contact_dnc_id' => $this->contact_dnc_id, 'group_id' => $group_id));
    $get_link_count = mysqli_query(DB::$link, "SELECT * from contact_link");
    $result_add = mysqli_query(DB::$link, "DELETE from contact_link where contact_dnc_id=".$this->contact_dnc_id." AND group_id=".$group_id);
    $result = mysqli_num_rows($get_link_count)-1;
    //$count_contact = mysqli_query(DB::$link, "SELECT * from contact_link where group_id=".$group_id." GROUP BY contact_dnc_id");
    //$cont_result =  mysqli_num_rows($count_contact);
    //$udate_group = mysqli_query(DB::$link, "UPDATE contact_group set contact_count=".$cont_result." where group_id=".$group_id);
    Corelog::log("group contacts Deleted: ", Corelog::CRUD);
    return $result ;
  }
}