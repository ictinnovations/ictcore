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
use ICT\Core\Session;
use ICT\Core\Token;
use ICT\Core\User;

class Template extends Message
{

  protected static $table = 'template';
  protected static $primary_key = 'template_id';
  protected static $fields = array(
      'template_id',
      'name',
      'type',
      'description',
      'subject',
      'body',
      'body_alt',
      'attachment',
      'length'
  );
  protected static $read_only = array(
      'template_id',
      'length'
  );

  /**
   * @property-read integer $template_id
   * @var integer 
   */
  protected $template_id = NULL;

  /** @var string */
  public $name = NULL;

  /** @var string */
  public $type = NULL;

  /** @var string */
  public $description = NULL;

  /** @var string */
  public $subject = NULL;

  /** @var string */
  public $body = NULL;

  /** @var string */
  public $body_alt = NULL;

  /**
   * @property integer $attachment
   * @see Template::set_attachment()
   * @var string 
   */
  protected $attachment = NULL;

  /**
   * @property-read integer $length
   * @var string 
   */
  protected $length = NULL;

  public function __construct($template_id = NULL)
  {
    $this->template_id = $template_id;
    parent::__construct($template_id);
    $this->message_id = &$this->template_id; // Assign by reference will keep both variable same
  }

  public static function construct_from_file($file_path)
  {
    $template = array(); // will be populated by following include
    include $file_path;
    return Template::construct_from_array($template);
  }

  public static function search($aFilter = array())
  {
    $aTemplate = array();
    $from_str = self::$table;
    $aWhere = array();
    foreach ($aFilter as $search_field => $search_value) {
      switch ($search_field) {
        case 'template_id':
          $aWhere[] = "$search_field = $search_value";
          break;
        case 'name':
        case 'subject':
        case 'type':
        case 'description':
        case 'body':
        case 'body_alt':
          $aWhere[] = "$search_field LIKE '%$search_value%'";
          break;
      }
    }
    if (!empty($aWhere)) {
      $from_str .= ' WHERE ' . implode(' AND ', $aWhere);
    }

    $query = "SELECT template_id, name, subject, type, description FROM " . $from_str;
    Corelog::log("template search with $query", Corelog::DEBUG, array('aFilter' => $aFilter));
    $result = DB::query('template', $query);
    while ($data = mysql_fetch_assoc($result)) {
      $aTemplate[] = $data;
    }

    return $aTemplate;
  }

  protected function load()
  {
    $query = "SELECT * FROM " . self::$table . " WHERE template_id='%template_id%' ";
    $result = DB::query(self::$table, $query, array('template_id' => $this->template_id), true);
    $data = mysql_fetch_assoc($result);
    if ($data) {
      $this->template_id = $data['template_id'];
      $this->name = $data['name'];
      $this->description = $data['description'];
      $this->subject = $data['subject'];
      $this->body = $data['body'];
      $this->body_alt = $data['body_alt'];
      $this->attachment = $data['attachment'];
      $this->type = $data['type'];
      $this->length = $data['length'];
      Corelog::log("Template loaded name: $this->name", Corelog::CRUD);
    } else {
      throw new CoreException('404', 'Template not found');
    }
  }

  public function delete()
  {
    Corelog::log("Template delete", Corelog::CRUD);
    return DB::delete(self::$table, 'template_id', $this->template_id, true);
  }

  protected function set_attachment($file_path)
  {
    global $path_data;
    if (file_exists($file_path)) {
      $oSession = Session::get_instance();
      $user_id = empty(User::$user) ? 0 : $oSession->user->user_id;
      $raw_type = strtolower(end(explode('.', $file_path)));
      $file_type = empty($file_type) ? 'pdf' : $raw_type;
      $file_name = 'attachment_' . $user_id . '_';
      $file_name .= DB::next_record_id($file_name);
      $dst_file = $path_data . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR . $file_name . '.' . $file_type;
      rename($file_path, $dst_file);
      $this->attachment = $dst_file;
    } else {
      $this->attachment = NULL;
      return; // invalid file
    }
  }

  public function save()
  {
    $attachment_data = '';
    if (!empty($this->attachment)) {
      $attachment_data = file_get_contents($this->attachment);
    }
    $this->length = strlen($this->body . $this->body_alt . $this->subject . $attachment_data);

    $data = array(
        'template_id' => $this->template_id,
        'name' => $this->name,
        'type' => $this->type,
        'description' => $this->description,
        'subject' => $this->subject,
        'body' => $this->body,
        'body_alt' => $this->body_alt,
        'attachment' => $this->attachment,
        'length' => $this->length
    );

    if (isset($data['template_id']) && !empty($data['template_id'])) {
      // update existing record
      $result = DB::update(self::$table, $data, 'template_id', true);
      Corelog::log("Template updated: $this->template_id", Corelog::CRUD);
    } else {
      // add new
      $result = DB::update(self::$table, $data, false, true);
      $this->template_id = $data['template_id'];
      Corelog::log("New Template created: $this->template_id", Corelog::CRUD);
    }
    return $result;
  }

  public function token_apply(Token $oToken, $default_value = Token::KEEP_ORIGNAL)
  {
    $this->subject = $oToken->render_variable($this->subject, $default_value);
    $this->body = $oToken->render_variable($this->body, $default_value);
    $this->body_alt = $oToken->render_variable($this->body_alt, $default_value);
    $this->attachment = $oToken->render_variable($this->attachment, $default_value);
  }

}