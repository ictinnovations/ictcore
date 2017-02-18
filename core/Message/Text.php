<?php

namespace ICT\Core\Message;

/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\CoreException;
use ICT\Core\Corelog;
use ICT\Core\DB;
use ICT\Core\Message;
use ICT\Core\Token;

class Text extends Message
{

  const ENCODING_GSM = 0;
  const ENCODING_BINARY = 1;
  const ENCODING_UNICODE = 2;
  
  const CLASS_DISABLE = 0;
  const CLASS_NORMAL = 1;
  const CLASS_SIM = 2;
  const CLASS_TOOLKIT = 4; // SIM TOOLKIT mclass actual value is 3, which can't be used in flag
  const CLASS_FLASH = 8; // FLASH mclass actual value is 0, which is being used to disable mclass

  protected static $table = 'text';
  protected static $primary_key = 'text_id';
  protected static $fields = array(
      'text_id',
      'name',
      'data',
      'type',
      'description',
      'length',
      'class',
      'encoding'
  );
  protected static $read_only = array(
      'text_id',
      'length',
      'class',
      'encoding'
  );

  /**
   * @property-read integer $text_id
   * @var integer
   */
  protected $text_id = NULL;

  /** @var string */
  public $name = NULL;

  /**
   * @property string $data 
   * @see Text::set_data()
   * @var string */
  public $data = NULL;

  /** @var string */
  public $type = 'UTF-8';

  /** @var string */
  public $description = NULL;

  /**
   * @property-read integer $length
   * @var integer 
   */
  protected $length = NULL;

  /**
   * @property-read integer $length
   * @var integer
   */
  protected $class = Text::CLASS_NORMAL;

  /**
   * @property-read integer $length
   * @var integer
   */
  protected $encoding = Text::ENCODING_GSM;

  public function __construct($text_id = NULL)
  {
    $this->text_id = $text_id;
    parent::__construct($text_id);
    $this->message_id = &$this->text_id; // Assign by reference will keep both variable same
  }

  public static function search($aFilter = array())
  {
    $aText = array();
    $from_str = self::$table;
    $aWhere = array();
    foreach ($aFilter as $search_field => $search_value) {
      switch ($search_field) {
        case 'text_id':
          $aWhere[] = "$search_field = $search_value";
          break;
        case 'name':
        case 'type':
        case 'description':
          $aWhere[] = "$search_field LIKE '%$search_value%'";
          break;
      }
    }
    if (!empty($aWhere)) {
      $from_str .= ' WHERE ' . implode(' AND ', $aWhere);
    }

    $query = "SELECT text_id, name, type, description FROM " . $from_str;
    Corelog::log("text search with $query", Corelog::DEBUG, array('aFilter' => $aFilter));
    $result = DB::query('text', $query);
    while ($data = mysql_fetch_assoc($result)) {
      $aText[$data['text_id']] = $data;
    }

    return $aText;
  }

  protected function load()
  {
    $query = "SELECT * FROM " . self::$table . " WHERE text_id='%text_id%' ";
    $result = DB::query(self::$table, $query, array('text_id' => $this->text_id), true);
    $data = mysql_fetch_assoc($result);
    if ($data) {
      $this->text_id = $data['text_id'];
      $this->name = $data['name'];
      $this->data = $data['data'];
      $this->type = $data['type'];
      $this->description = $data['description'];
      $this->length = $data['length'];
      $this->class = $data['class'];
      $this->encoding = $data['encoding'];
      Corelog::log("Text loaded name: $this->name", Corelog::CRUD);
    } else {
      throw new CoreException('404', 'Transmission not found');
    }
  }

  public function delete()
  {
    Corelog::log("Text delete", Corelog::CRUD);
    return DB::delete(self::$table, 'text_id', $this->text_id, true);
  }

  protected function set_data($data)
  {
    $this->data = $data;
    $this->length = strlen($data);
  }

  public function save()
  {
    $data = array(
        'text_id' => $this->text_id,
        'name' => $this->name,
        'data' => $this->data,
        'type' => $this->type,
        'description' => $this->description,
        'length' => $this->length,
        'class' => $this->class,
        'encoding' => $this->encoding
    );

    if (isset($data['text_id']) && !empty($data['text_id'])) {
      // update existing record
      $result = DB::update(self::$table, $data, 'text_id', true);
      Corelog::log("Text updated: $this->text_id", Corelog::CRUD);
    } else {
      // add new
      $result = DB::update(self::$table, $data, false, true);
      $this->text_id = $data['text_id'];
      Corelog::log("New Text created: $this->text_id", Corelog::CRUD);
    }
    return $result;
  }

  public function token_apply(Token $oToken, $default_value = Token::KEEP_ORIGNAL)
  {
    $this->data = $oToken->render_variable($this->data, $default_value);
  }

}