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
      'quality',
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
   * @property integer $quality
   * Quality of document
   * @param string("basic", "standard", "fine", "super", "superior", "ultra") $quality
   */
  public $quality = 'standard';

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
      'tiff' => 'image/x-tiff',
      'png'  => 'image/png',
      'jpg'  => 'image/jpeg',
      'jpeg' => 'image/x-citrix-jpeg',
      /*
      'txt'  => 'text/plain',
      'text' => 'text/plain',
      'htm'  => 'text/htm',
      'html' => 'text/html',
      */
      // office files
      'ppt'  => 'application/vnd.ms-powerpoint',
      'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
      'odp'  => 'application/vnd.oasis.opendocument.presentation',
      'doc'  => 'application/msword',
      'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
      'odt'  => 'application/vnd.oasis.opendocument.text',
      'xls'  => 'application/vnd.ms-excel',
      'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      'ods'  => 'application/vnd.oasis.opendocument.spreadsheet'
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

    $query = "SELECT document_id, name, file_name, type, pages, description FROM " . $from_str;
    Corelog::log("document search with $query", Corelog::DEBUG, array('aFilter' => $aFilter));
    $result = DB::query('document', $query);
    while ($data = mysqli_fetch_assoc($result)) {
      $aDocument[] = $data;
    }

    return $aDocument;
  }

  protected function load()
  {
    $query = "SELECT * FROM " . self::$table . " WHERE document_id='%document_id%' ";
    $result = DB::query(self::$table, $query, array('document_id' => $this->document_id));
    $data = mysqli_fetch_assoc($result);
    if ($data) {
      $this->document_id = $data['document_id'];
      $this->name = $data['name'];
      $this->file_name = $data['file_name'];
      $this->type = $data['type'];
      $this->description = $data['description'];
      $this->pages = $data['pages'];
      $this->size_x = $data['size_x'];
      $this->size_y = $data['size_y'];
      $this->quality = $data['quality'];
      $this->resolution_x = $data['resolution_x'];
      $this->resolution_y = $data['resolution_y'];
      $this->user_id = $data['created_by'];
      Corelog::log("Document loaded name: $this->name", Corelog::CRUD);
    } else {
      throw new CoreException('404', 'Document not found');
    }
  }

  public function delete()
  {
    Corelog::log("Document delete", Corelog::CRUD);
    return DB::delete(self::$table, 'document_id', $this->document_id);
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
      // create new file
      $file_type = empty($this->type) ? 'pdf' : $this->type;
      $file_name = 'document_' . $user_id . '_';
      $file_name .= DB::next_record_id($file_name);
      $tiff_file = $path_data . DIRECTORY_SEPARATOR . 'document' . DIRECTORY_SEPARATOR . $file_name . '.tif';
    }

    foreach($aFile as $file) {
      $aType = explode('.', $file);
      $file_type = empty($this->type) ? end($aType) : $this->type;
      $file_type = strtolower($file_type);
      $this->create_tiff($file, $file_type, $tiff_file, $this->quality); // it will append new tiff file into $tiff_file
    }
    $this->type = $file_type;
    $this->file_name = $tiff_file;
  }

  protected function set_quality($quality) {
    switch ($quality) {
      case 'basic':
        $this->resolution_x = 100;
        $this->resolution_y = 98;  // or 100
        break;
      case 'standard':
        $this->resolution_x = 204; // or 200
        $this->resolution_y = 98;  // or 100
        break;
      case 'fine':
        $this->resolution_x = 204; // or 200
        $this->resolution_y = 196; // or 200
        break;
      case 'super':
        $this->resolution_x = 204; // or 200
        $this->resolution_y = 391; // or 400
        break;
      case 'superior':
        $this->resolution_x = 300;
        $this->resolution_y = 300;
        break;
      case 'ultra':
        $this->resolution_x = 408; // or 400
        $this->resolution_y = 391; // or 400
        break;
      default:
        break;
    }
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
        'quality' => $this->quality,
        'resolution_x' => $this->resolution_x,
        'resolution_y' => $this->resolution_y,
        'pages' => $this->pages
    );

    if (isset($data['document_id']) && !empty($data['document_id'])) {
      // update existing record
      $result = DB::update(self::$table, $data, 'document_id');
      Corelog::log("Document updated: $this->document_id", Corelog::CRUD);
    } else {
      // add new
      $result = DB::update(self::$table, $data, false);
      $this->document_id = $data['document_id'];
      Corelog::log("New Document created: $this->document_id", Corelog::CRUD);
    }
    return $result;
  }

  private function create_tiff($sourceFile, $type, $targetFile, $quality = 'standard')
  {
    $this->pages = 0;
    $this->size_y = 0;
    $this->size_x = 0;

    $this->set_quality($quality);

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

    // some time simple pdf to tiff conversion can create problem in fax sending, I don't why? probably we need a raster image then vector
    // but ps to tiff conversion can solve this problem
    // also it will fix the image size and orientation issue
    global $path_etc;
    $config = "$path_etc/postscript/rotate.ps";
    $resolution_string = $this->resolution_x . "x" . $this->resolution_y;
    $cmd = \ICT\Core\sys_which('gs', '/usr/bin') . " -q -dNOPAUSE -dBATCH -P- -dSAFER -sDEVICE=ps2write -r$resolution_string -sOutputFile='$pdfFile.ps' -c save pop -f '$config' '$pdfFile'";
    exec($cmd);

    //$cmd = "convert -quiet -density -threshold 85% 150 $sourceFile -shave 65x65 -colorspace rgb -quality 100 -resample 320 $targetFile";
    // for monochrome (black/wite) color
    //$mono = ' -c "<< /HalftoneMode 1 >> setuserparams"';
    //$mono = ' -dDITHER=300 -Ilib stocht.ps -c "{ dup .9 lt { pop 0 } if } settransfer"';
    $mono = ' -dDITHER=300 -c "{ dup .85 lt { pop 0 } if } settransfer"'; // ref https://bugs.ghostscript.com/show_bug.cgi?id=694762

      $cmd  = \ICT\Core\sys_which('gs', '/usr/bin') . " -dBATCH -dNOPAUSE -sDEVICE=tiffg3 -sOutputFile='$targetFile.tmp' $mono -f '$pdfFile.ps'";
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
        $cmd = \ICT\Core\sys_which('convert', '/usr/bin') . " $sourceFile $pdfFile";
        exec($cmd);
        //exec("rm -rf '$sourceFile'");
        break;
      case 'pptx':
      case 'ppt':
      case 'odp':
      case 'docx':
      case 'doc':
      case 'odt':
      case 'xlsx':
      case 'xls':
      case 'ods':
        Corelog::log("Converting office document into pdf", Corelog::CRUD);
        global $path_cache;
        $home_dir = $path_cache; // home directory is required to save / read office configurations
        $target_dir = pathinfo($sourceFile, PATHINFO_DIRNAME); // use source directory as target directory
        $include_path = '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin'; // required to include all required jvm
        $office_binary = \ICT\Core\sys_which('libreoffice', '/usr/bin');
        $result = exec("export HOME=$home_dir && export PATH=$include_path && $office_binary --headless --convert-to pdf $sourceFile  --outdir $target_dir");
        $pdfFile = str_replace(pathinfo($sourceFile, PATHINFO_EXTENSION), 'pdf', $sourceFile);
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
