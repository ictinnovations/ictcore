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
  private $contact_id = NULL;

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

  public static function search($aFilter = array())
  {
    $aContact = array();
    $from_str = self::$table;
    $aWhere = array();
    foreach ($aFilter as $search_field => $search_value) {
      switch ($search_field) {
        case 'contact_id':
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
      }
    }
    if (!empty($aWhere)) {
      $from_str .= ' WHERE ' . implode(' AND ', $aWhere);
    }

    $query = "SELECT contact_id, first_name, last_name, phone, email FROM " . $from_str;
    Corelog::log("contact search with $query", Corelog::DEBUG, array('aFilter' => $aFilter));
    $result = DB::query('contact', $query);
    while ($data = mysql_fetch_assoc($result)) {
      $aContact[] = $data;
    }

    // if no contact found, check for special contacts
    if (empty($aContact) && isset($aFilter['contact_id']) && $aFilter['contact_id'] == Contact::COMPANY) {
      $oContact = new Contact($aFilter['contact_id']);
      $aContact[$oContact->contact_id] = array(
          'contact_id' => $oContact->contact_id,
          'first_name' => $oContact->first_name,
          'last_name' => $oContact->last_name,
          'phone' => $oContact->phone,
          'email' => $oContact->email
      );
    }

    return $aContact;
  }

  private function load()
  {
    $query = "SELECT * FROM " . self::$table . " WHERE contact_id='%contact_id%' ";
    $result = DB::query(self::$table, $query, array('contact_id' => $this->contact_id), true);
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
      Corelog::log("Contact loaded name: $this->first_name $this->last_name", Corelog::CRUD);
    } else {
      throw new CoreException('404', 'Contact not found');
    }
  }

  public function delete()
  {
    Corelog::log("Contact delete", Corelog::CRUD);
    return DB::delete(self::$table, 'contact_id', $this->contact_id, true);
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
      $result = DB::update(self::$table, $data, 'contact_id', true);
      Corelog::log("Contact updated: $this->contact_id", Corelog::CRUD);
    } else {
      // add new
      $result = DB::update(self::$table, $data, false, true);
      $this->contact_id = $data['contact_id'];
      Corelog::log("New Contact created: $this->contact_id", Corelog::CRUD);
    }
    return $result;
  }

}
