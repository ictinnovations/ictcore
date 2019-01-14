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
use ICT\Core\User;
use NcJoes\OfficeConverter\OfficeConverter;

class Document extends Message
{

  /** @const */
  protected static $table = 'document';
  protected static $primary_key = 'document_id';
  protected static $fields = array(
      'document_id',
      'name',
      'file_name',
      'type',
      'description',
      'pages',
      'size_x',
      'size_y',
      'resolution_x',
      'resolution_y'
  );
  protected static $read_only = array(
      'document_id',
      'size_x',
      'size_y',
      'resolution_x',
      'resolution_y',
      'pages'
  );

  /**
   * @property-read integer $document_id
   * @var integer
   */
  protected $document_id = NULL;

  /** @var string */
  public $name = NULL;

  /**
   * @property string $file_name
   * @see Document::set_file_name()
   * @var string 
   */
  protected $file_name = NULL;

  /**
   * @property-read string $link
   * @see Document::get_link()
   */

  /** @var string */
  protected $type = NULL;

  /** @var string */
  public $description = NULL;

  /**
   * @property-read integer $pages
   * @var integer
   */
  protected $pages = NULL;

  /**
   * @property-read integer $size_x
   * @var integer
   */
  protected $size_x = NULL;

  /**
   * @property-read integer $size_y
   * @var integer
   */
  protected $size_y = NULL;

  /**
   * @property-read integer $resolution_x
   * all possible values are
   * * 100
   * * 200 or 204
   * * 400 or 408
   * @var integer
   */
  protected $resolution_x = 204;

  /**
   * @property-read integer $resolution_y
   * other possible values are
   * for width : 100
   * * 100
   * for width : 200 or 204
   * * 98 or 100
   * * 196 or 200
   * * 391 or 400
   * for width : 400 or 408
   * * 391 or 400
   * @var integer
   */
  protected $resolution_y = 98;

  /**
   * Default mime type for this message type, when no type is available
   * @var string
   */
  public static $media_default = 'application/pdf';

  /**
   * Array of all supported file extensions along with mime types as keys
   * @var array $media_supported
   */
  public static $media_supported = array(
      'pdf'  => 'application/pdf',
      'tif'  => 'image/tiff',
      'tiff' => 'image/x-tiff'
  );

  public function __construct($document_id = NULL)
  {
    $this->document_id = $document_id;
    parent::__construct($document_id);
    $this->message_id = &$this->document_id; // Assign by reference will keep both variable same
  }

  public static function search($aFilter = array())
  {
    $aDocument = array();
    $from_str = self::$table;
    $aWhere = array();
    foreach ($aFilter as $search_field => $search_value) {
      switch ($search_field) {
        case 'document_id':
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

    $query = "SELECT document_id, name, file_name, type, pages, description FROM " . $from_str;
    Corelog::log("document search with $query", Corelog::DEBUG, array('aFilter' => $aFilter));
    $result = DB::query('document', $query);
    while ($data = mysql_fetch_assoc($result)) {
      $aDocument[] = $data;
    }

    return $aDocument;
  }

  protected function load()
  {
    $query = "SELECT * FROM " . self::$table . " WHERE document_id='%document_id%' ";
    $result = DB::query(self::$table, $query, array('document_id' => $this->document_id), true);
    $data = mysql_fetch_assoc($result);
    if ($data) {
      $this->document_id = $data['document_id'];
      $this->name = $data['name'];
      $this->file_name = $data['file_name'];
      $this->type = $data['type'];
      $this->description = $data['description'];
      $this->pages = $data['pages'];
      $this->size_x = $data['size_x'];
      $this->size_y = $data['size_y'];
      $this->resolution_x = $data['resolution_x'];
      $this->resolution_y = $data['resolution_y'];
      Corelog::log("Document loaded name: $this->name", Corelog::CRUD);
    } else {
      throw new CoreException('404', 'Document not found');
    }
  }

  public function delete()
  {
    Corelog::log("Document delete", Corelog::CRUD);
    return DB::delete(self::$table, 'document_id', $this->document_id, true);
  }

  protected function set_file_name($file_path)
  {
    global $path_data;
    $oSession = Session::get_instance();
    $user_id = empty(User::$user) ? 0 : $oSession->user->user_id;

    $aFile = \ICT\Core\path_string_to_array($file_path);
    if (!empty($this->file_name) && in_array($this->file_name, $aFile)) {
      $file_type = $this->type;
      $tiff_file = $this->file_name;
      $pos = array_search($tiff_file, $aFile);
      unset($aFile[$pos]);
    } else {
      $file_type = empty($this->type) ? 'wav' : $this->type;
      $file_name = 'document_' . $user_id . '_';
      $file_name .= DB::next_record_id($file_name);
      $tiff_file = $path_data . DIRECTORY_SEPARATOR . 'document' . DIRECTORY_SEPARATOR . $file_name . '.tif';
    }

    foreach($aFile as $file) {
      $aType = explode('.', $file);
      $file_type = isset($this->type) ? $this->type : end($aType);
      $file_type = strtolower($file_type);
      $this->create_tiff($file, $file_type, $tiff_file); // it will append new tiff file into $tiff_file
    }
    $this->type = $file_type;
    $this->file_name = $tiff_file;
  }

  public function save()
  {
    $data = array(
        'document_id' => $this->document_id,
        'name' => $this->name,
        'file_name' => $this->file_name,
        'type' => $this->type,
        'description' => $this->description,
        'size_x' => $this->size_x,
        'size_y' => $this->size_y,
        'resolution_x' => $this->resolution_x,
        'resolution_y' => $this->resolution_y,
        'pages' => $this->pages
    );

    if (isset($data['document_id']) && !empty($data['document_id'])) {
      // update existing record
      $result = DB::update(self::$table, $data, 'document_id', true);
      Corelog::log("Document updated: $this->document_id", Corelog::CRUD);
    } else {
      // add new
      $result = DB::update(self::$table, $data, false, true);
      $this->document_id = $data['document_id'];
      Corelog::log("New Document created: $this->document_id", Corelog::CRUD);
    }
    return $result;
  }

  private function create_tiff($sourceFile, $type, $targetFile)
  {
    $this->pages = 0;
    $this->size_y = 0;
    $this->size_x = 0;

    $infos = '';
    $pdfFile = $this->create_pdf($sourceFile, $type);
    exec(\ICT\Core\sys_which('pdfinfo', '/usr/bin') . " '$pdfFile'", $infos);
    foreach ($infos as $info_row) {
      $matches = array();
      if (preg_match('/^Pages:\s*([0-9.]*)$/', $info_row, $matches)) {
        $this->pages = $matches[1];
      } else if (preg_match('/^Page size:\s*([0-9.]*) x ([0-9.]*)/', $info_row, $matches)) {
        $this->size_y = round($matches[1]);
        $this->size_x = round($matches[2]);
      }
    }

    $page_arg = '';
    if ($this->size_y > 595 && $this->size_x < $this->size_y) {
      // rotate
      $page_arg .= " -c \"<</Orientation 0>> setpagedevice\"";
      // swap
      list($this->size_y, $this->size_x) = array($this->size_x, $this->size_y);
    }

    if ($this->size_y != 595 || $this->size_x > 842) {
      if ($this->size_x > 842) {
        $this->size_x = 842; // height must be limited to A4 height (842)
      }
      $this->size_y = 595;   // width must be EQUAL to A4 width (595)
    }

    // some time simple pdf to tiff conversion can create problem in fax sending, I don't why? 
    // but ps to tiff conversion can solve this problem
    $cmd = \ICT\Core\sys_which('pdf2ps', '/usr/bin') . " '$pdfFile' '$pdfFile.ps'";
    exec($cmd);

    $resolution_string = $this->resolution_x . "x" . $this->resolution_y;
    //$cmd = "convert -quiet -density 150 $sourceFile -shave 65x65 -colorspace rgb -quality 100 -resample 320 $targetFile";
    //$cmd = "cat $sourceFile | gs -q -sDEVICE=tiffg3 -sPAPERSIZE=a4 -r204x196 -dNOPAUSE -sOutputFile=$targetFile"; // 
    $cmd = \ICT\Core\sys_which('gs', '/usr/bin') . " -dBATCH -dNOPAUSE -sDEVICE=tiffg3 -r$resolution_string -sOutputFile='$targetFile.tmp' -dFIXEDMEDIA -dDEVICEWIDTHPOINTS=$this->size_y -dDEVICEHEIGHTPOINTS=$this->size_x -f '$pdfFile.ps'";
    Corelog::log("Converting source image into fax support tiff", Corelog::CRUD, $cmd);
    exec($cmd);
    //exec("rm -rf '$sourceFile'");
    // -a for append and -t for tiles i.e pages in correct sequence like A1,A2,A3,B1,B2,C1,C2,C3
    $cmd = \ICT\Core\sys_which('tiffcp', '/usr/bin') . " -x -a '$targetFile.tmp' '$targetFile'";
    exec($cmd);
    exec("rm -rf '$targetFile.tmp'");

    return $this->pages;
  }

  public static function create_pdf($sourceFile, $type = '')
  {
    switch ($type) {
      case 'tif':
      case 'tiff':
        Corelog::log("Converting tif/tiff into pdf", Corelog::CRUD);
        $pdfFile = "$sourceFile.pdf";
        $cmd = \ICT\Core\sys_which('tiff2pdf', '/usr/bin') . " -o $pdfFile -z $sourceFile";
        exec($cmd);
        //exec("rm -rf '$sourceFile'");
        break;
      case 'htm':
      case 'html':
        Corelog::log("Converting htm/html into pdf", Corelog::CRUD);
        $pdfFile = "$sourceFile";
        //TODO: add html file support
        break;
      case 'txt':
      case 'text':
        Corelog::log("Converting txt/text into pdf", Corelog::CRUD);
        $pdfFile = "$sourceFile.pdf";
        $cmd = \ICT\Core\sys_which('textfmt', 'usr/sbin') . " $sourceFile > $sourceFile.ps";
        //$cmd = "/usr/local/bin/uniprint -hsize 0 -size 9 -in $sourceFile.pdf -out $sourceFile.ps";
        exec($cmd);
        //exec("rm -rf '$sourceFile'");
        $cmd = \ICT\Core\sys_which('ps2pdf', '/usr/bin') . " $sourceFile.ps $pdfFile";
        exec($cmd);
        //exec("rm -rf '$sourceFile.ps'");
        break;
      case 'png':
      case 'jpg':
      case 'jpeg':
        Corelog::log("Converting png/jpg into pdf", Corelog::CRUD);
        $pdfFile = "$sourceFile.pdf";
        $cmd = \ICT\Core\sys_which('convert', '/usr/bin') . " $sourceFile -type Bilevel -monochrome $pdfFile";
        exec($cmd);
        //exec("rm -rf '$sourceFile'");
        break;
      case 'pptx':
      case 'ppt':
      case 'docx':
      case 'doc':
      case 'xlsx':
      case 'xls':
        Corelog::log("Converting office document into pdf", Corelog::CRUD);
        $office_binary = \ICT\Core\sys_which('libreoffice', '/usr/bin');
        $target_dir = dirname($sourceFile);
        $target_file = basename($sourceFile).'.pdf';
        $oConverter = new OfficeConverter($sourceFile, $target_dir, $office_binary);
        $pdfFile = $oConverter->convertTo($target_file);
        break;
      default:
        Corelog::log("Unknown file type assume it as pdf", Corelog::CRUD);
        $pdfFile = "$sourceFile";
        break;
    }
    return $pdfFile;
  }

  public function get_pdf_file()
  {
    return $this->create_pdf($this->file_name, $this->type);
  }

  public function get_link()
  {
    return $this->file_name;
  }

}
