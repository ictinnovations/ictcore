<?php

namespace ICT\Core;

/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

class Contact
{

  /** @const */
  const COMPANY = -2;

  private static $table = 'contact';
  private static $table_link = 'contact_link';
  private static $primary_key = 'contact_id';
  private static $fields = array(
      'contact_id',
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
      'contact_id'
  );

  /**
   * @property-read integer $contact_id
   * @var integer
   */
  public $contact_id = NULL;

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

  public function __construct($contact_id = NULL)
  {
    if (!empty($contact_id) && $contact_id > 0) {
      $this->contact_id = $contact_id;
      $this->load();
    } else if (Contact::COMPANY == $contact_id) {
      $this->contact_id = $contact_id;
      $title = Conf::get('company:title', 'ICTCore');
      $aTitle = explode(' ', $title, 2);
      $this->first_name = $aTitle[0];
      $this->last_name = isset($aTitle[1]) ? $aTitle[1] : '';
      $this->email = Conf::get('company:email', 'no-reply@example.com');
      $this->phone = Conf::get('company:phone', '1111111111');
      $this->address = Conf::get('company:address', 'PK');
    }
  }

  public static function construct_from_array($aContact)
  {
    $oContact = new Contact();
    foreach ($aContact as $field => $value) {
      $oContact->$field = $value;
    }
    return $oContact;
  }

  public static function search($aFilter = array(), $full = false)
  {
    $aContact = array();
    $from_str = self::$table." c LEFT JOIN ".self::$table_link." l ON c.contact_id=l.contact_id";
    $aWhere = array();
    foreach ($aFilter as $search_field => $search_value) {
      switch ($search_field) {
        case 'group_id':
          $aWhere[] = "l.$search_field = $search_value";
          break;
        case 'contact_id':
          $aWhere[] = "c.$search_field = $search_value";
          break;
        case 'phone':
          $aWhere[] = "c.$search_field LIKE '%$search_value'";
          break;
        case 'email':
          $aWhere[] = "c.$search_field = '$search_value'";
          break;
        case 'first_name':
        case 'last_name':
          $aWhere[] = "c.$search_field LIKE '%$search_value%'";
          break;

        case 'user_id':
        case 'created_by':
          $aWhere[] = "c.created_by = '$search_value'";
          break;
        case 'before':
          $aWhere[] = "c.date_created <= $search_value";
          break;
        case 'after':
          $aWhere[] = "c.date_created >= $search_value";
          break;
      }
    }
    if (!empty($aWhere)) {
      $from_str .= ' WHERE ' . implode(' AND ', $aWhere);
    }

    if ($full) {
      $query = "SELECT c.contact_id, c.first_name, c.last_name, c.phone, c.email, c.address, "
              ."c.custom1, c.custom2, c.custom3, c.description FROM " . $from_str;
    } else {
      $query = "SELECT c.contact_id, c.first_name, c.last_name, c.phone, c.email FROM " . $from_str;
    }
    Corelog::log("contact search with $query", Corelog::DEBUG, array('aFilter' => $aFilter));
    $result = DB::query('contact', $query);
    while ($data = mysql_fetch_assoc($result)) {
      $aContact[] = $data;
    }

    // if no contact found, check for special contacts
    if (empty($aContact) && isset($aFilter['contact_id']) && $aFilter['contact_id'] == Contact::COMPANY) {
      $oContact = new Contact($aFilter['contact_id']);
      $singleContact['contact_id'] = $oContact->contact_id;
      $singleContact['first_name'] = $oContact->first_name;
      $singleContact['last_name'] = $oContact->last_name;
      $singleContact['phone'] = $oContact->phone;
      $singleContact['email'] = $oContact->email;
      if ($full) {
        $singleContact['address'] = '';
        $singleContact['custom1'] = '';
        $singleContact['custom2'] = '';
        $singleContact['custom3'] = '';
        $singleContact['descritpion'] = '';
      }
      $aContact = $singleContact;
    }

    return $aContact;
  }

  private function load()
  {
    $query = "SELECT * FROM " . self::$table . " WHERE contact_id='%contact_id%' ";
    $result = DB::query(self::$table, $query, array('contact_id' => $this->contact_id));
    $data = mysql_fetch_assoc($result);
    if ($data) {
      $this->contact_id = $data['contact_id'];
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
      Corelog::log("Contact loaded name: $this->first_name $this->last_name", Corelog::CRUD);
    } else {
      throw new CoreException('404', 'Contact not found');
    }
  }

  public function delete()
  {
    Corelog::log("Contact delete", Corelog::CRUD);
    mysql_query("DELETE from contact_link where contact_id=".$this->contact_id);
    DB::delete(self::$table_link, 'contact_id', $this->contact_id);
    return DB::delete(self::$table, 'contact_id', $this->contact_id);
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
    return $this->contact_id;
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
        'contact_id' => $this->contact_id,
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

    if (isset($data['contact_id']) && !empty($data['contact_id'])) {
      // update existing record
      $result = DB::update(self::$table, $data, 'contact_id');
      Corelog::log("Contact updated: $this->contact_id", Corelog::CRUD);
    } else {
      // add new
      $result = DB::update(self::$table, $data, false);
      $this->contact_id = $data['contact_id'];
      Corelog::log("New Contact created: $this->contact_id", Corelog::CRUD);
    }
    return $result;
  }


  public function link($group_id)
  {
    // add new
    $link = array(
        'contact_id' => $this->contact_id,
        'group_id' => $group_id
    );
    return DB::update(self::$table_link, $link);
  }

  public function link_delete($group_id = null)
  {
    if ($group_id == null) {
      $link_delete_query = "DELECT FROM ".self::$table_link." WHERE contact_id=%contact_id%";
    } else {
      $link_delete_query = "DELETE FROM ".self::$table_link." WHERE contact_id=%contact_id% AND group_id=%group_id%";
    }
    DB::query(self::$table, $req_query, array('contact_id' => $this->contact_id, 'group_id' => $group_id));
    $get_link_count = mysql_query("SELECT * from contact_link");
    $result_add = mysql_query("DELETE from contact_link where contact_id=".$this->contact_id." AND group_id=".$group_id);
    $result = mysql_num_rows($get_link_count)-1;
    //$count_contact = mysql_query("SELECT * from contact_link where group_id=".$group_id." GROUP BY contact_id");
    //$cont_result =  mysql_num_rows($count_contact);
    //$udate_group = mysql_query("UPDATE contact_group set contact_count=".$cont_result." where group_id=".$group_id);
    Corelog::log("group contacts Deleted: ", Corelog::CRUD);
    return $result ;
  }

}
