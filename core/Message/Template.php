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
  const ATTACHMENT_MAXIMUM = 10;

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
  protected $aAttachment = array();

  /**
   * @property-read integer $length
   * @var string 
   */
  protected $length = NULL;

  /**
   * Default mime type for this message type, when no type is available
   * @var string
   */
  public static $media_default = 'text/html';

  /**
   * Array of all supported file extensions along with mime types as keys
   * @var array $media_supported
   */
  public static $media_supported = array(
      'txt' => 'text/plain',
      'html' => 'text/html',
      'xml' => 'application/xml'
  );

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

    $query = "SELECT template_id, name, subject, type, length, description FROM " . $from_str;
    Corelog::log("template search with $query", Corelog::DEBUG, array('aFilter' => $aFilter));
    $result = DB::query('template', $query);
    while ($data = mysqli_fetch_assoc($result)) {
      $aTemplate[] = $data;
    }

    return $aTemplate;
  }

  protected function load()
  {
    $query = "SELECT * FROM " . self::$table . " WHERE template_id='%template_id%' ";
    $result = DB::query(self::$table, $query, array('template_id' => $this->template_id));
    $data = mysqli_fetch_assoc($result);
    if ($data) {
      $this->template_id = $data['template_id'];
      $this->name = $data['name'];
      $this->description = $data['description'];
      $this->subject = $data['subject'];
      $this->body = $data['body'];
      $this->body_alt = $data['body_alt'];
      $this->aAttachment = json_decode($data['attachment']);
      $this->attachment = $this->get_attachment();
      $this->type = $data['type'];
      $this->length = $data['length'];
      $this->user_id = $data['created_by'];
      Corelog::log("Template loaded name: $this->name", Corelog::CRUD);
    } else {
      throw new CoreException('404', 'Template not found');
    }
  }

  public function delete()
  {
    Corelog::log("Template delete", Corelog::CRUD);
    return DB::delete(self::$table, 'template_id', $this->template_id);
  }

  protected function get_attachment() {
    return \ICT\Core\path_array_to_string($this->aAttachment);
  }

  protected function set_attachment($file_path)
  {
    global $path_data;
    $oSession = Session::get_instance();
    $user_id = empty(User::$user) ? 0 : $oSession->user->user_id;

    $currentAttachment = \ICT\Core\path_string_to_array($this->attachment);
    $newAttachment = \ICT\Core\path_string_to_array($file_path);
    $deleteFile = array_diff($currentAttachment, $newAttachment);
    $addFile = array_diff($newAttachment, $currentAttachment);

    // remove unlisted attachments
    foreach ($deleteFile as $attachment) {
      $pos = array_search($attachment, $currentAttachment);
      unset($currentAttachment[$pos]);
    }
    $this->aAttachment = $currentAttachment;

    foreach($addFile as $attachment) {
      if (file_exists($attachment)) {
        $file_parts = explode('.', $attachment);
        $raw_type = strtolower(end($file_parts));
        $file_type = empty($raw_type) ? 'html' : $raw_type;
        $file_name = 'attachment_' . $user_id . '_';
        $file_name .= DB::next_record_id($file_name);
        $dst_file = $path_data . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR . $file_name . '.' . $file_type;
        rename($attachment, $dst_file);
        $this->aAttachment[] = $dst_file;
      }
    }

    $this->attachment = \ICT\Core\path_array_to_string($this->aAttachment);
  }

  public function save()
  {
    $this->length = strlen($this->body . $this->body_alt . $this->subject);
    foreach($this->aAttachment as $attachment) {
      if (file_exists($attachment)) {
        $attachment_data = file_get_contents($attachment);
        $this->length += strlen($attachment_data);
      }
    }

    $data = array(
        'template_id' => $this->template_id,
        'name' => $this->name,
        'type' => $this->type,
        'description' => $this->description,
        'subject' => $this->subject,
        'body' => $this->body,
        'body_alt' => $this->body_alt,
        'attachment' => json_encode($this->aAttachment),
        'length' => $this->length
    );

    if (isset($data['template_id']) && !empty($data['template_id'])) {
      // update existing record
      $result = DB::update(self::$table, $data, 'template_id');
      Corelog::log("Template updated: $this->template_id", Corelog::CRUD);
    } else {
      // add new
      $result = DB::update(self::$table, $data, false);
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
