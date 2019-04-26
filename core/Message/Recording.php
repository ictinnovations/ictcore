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
use ICT\Core\User;
use ICT\Core\Session;

class Recording extends Message
{

  protected static $table = 'recording';
  protected static $primary_key = 'recording_id';
  protected static $fields = array(
      'recording_id',
      'name',
      'file_name',
      'type',
      'description',
      'length',
      'codec',
      'channel',
      'sample',
      'bitrate'
  );
  protected static $read_only = array(
      'recording_id',
      'length',
      'codec',
      'channel',
      'sample',
      'bitrate',
      'link',
      'message_id'
  );

  /**
   * @property-read integer $recording_id
   * @var integer
   */
  protected $recording_id = NULL;

  /** @var string */
  public $name = NULL;

  /**
   * @property string $file_name
   * @see Recording::set_file_name()
   * @var string 
   */
  public $file_name = NULL;

  /**
   * @property-read string $link
   * @see Recording::get_link()
   */

  /** @var string */
  protected $type = NULL;

  /** @var string */
  public $description = NULL;

  /**
   * @property-read integer $length
   * @var integer
   */
  protected $length = NULL;

  /**
   * @property-read string $codec
   * @var string
   */
  protected $codec = 'pcm';

  /**
   * @property-read integer $channel
   * @var integer
   */
  protected $channel = 1;

  /**
   * @property-read integer $sample
   * @var integer
   */
  protected $sample = 8000;

  /**
   * @property-read integer $bitrate
   * @var integer
   */
  protected $bitrate = 16;

  /**
   * Default mime type for this message type, when no type is available
   * @var string
   */
  public static $media_default = 'audio/wav';

  /**
   * Array of all supported file extensions along with mime types as keys
   * @var array $media_supported
   */
  public static $media_supported = array(
      'wav' => 'audio/wav',
      'wave' => 'audio/x-wav',
      'gsm' => 'audio/x-gsm',
      'mp3' => 'audio/mpeg3',
      'mpeg3' => 'audio/x-mpeg-3'
  );

  public function __construct($recording_id = NULL)
  {
    $this->recording_id = $recording_id;
    parent::__construct($recording_id);
    $this->message_id = &$this->recording_id; // Assign by reference will keep both variable same
  }

  public static function search($aFilter = array())
  {
    $aRecording = array();
    $from_str = self::$table;
    $aWhere = array();
    foreach ($aFilter as $search_field => $search_value) {
      switch ($search_field) {
        case 'recording_id':
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

    $query = "SELECT recording_id, name, file_name, type, length, description FROM " . $from_str;
    Corelog::log("recording search with $query", Corelog::DEBUG, array('aFilter' => $aFilter));
    $result = DB::query('recording', $query);
    while ($data = mysql_fetch_assoc($result)) {
      $aRecording[] = $data;
    }

    return $aRecording;
  }

  protected function load()
  {
    $query = "SELECT * FROM " . self::$table . " WHERE recording_id='%recording_id%' ";
    $result = DB::query(self::$table, $query, array('recording_id' => $this->recording_id));
    $data = mysql_fetch_assoc($result);
    if ($data) {
      $this->recording_id = $data['recording_id'];
      $this->name = $data['name'];
      $this->file_name = $data['file_name'];
      $this->type = $data['type'];
      $this->description = $data['description'];
      $this->length = $data['length'];
      $this->codec = $data['codec'];
      $this->channel = $data['channel'];
      $this->sample = $data['sample'];
      $this->bitrate = $data['bitrate'];
      $this->user_id = $data['created_by'];
      Corelog::log("Recording loaded name: $this->name", Corelog::CRUD);
    } else {
      throw new CoreException('404', 'Recording not found');
    }
  }

  public function delete()
  {
    Corelog::log("Recording delete", Corelog::CRUD);
    return DB::delete(self::$table, 'recording_id', $this->recording_id);
  }

  protected function set_file_name($file_path)
  {
    global $path_data;
    $oSession = Session::get_instance();
    $user_id = empty(User::$user) ? 0 : $oSession->user->user_id;

    $aFile = \ICT\Core\path_string_to_array($file_path);
    $aRecording = array();
    if (!empty($this->file_name) && in_array($this->file_name, $aFile)) {
      $file_type = $this->type;
      $wav_file = $this->file_name;
      // rename exsting recording, to make $wav_file empty
      $new_file = tempnam('/tmp', 'tmp_recording') . '.wav';
      rename($wav_file, $new_file);
      $pos = array_search($tiff_file, $aFile);
      unset($aFile[$pos]);
      // start aRecording with existing recording
      $aRecording[] = $new_file;
    } else {
      $file_type = empty($this->type) ? 'wav' : $this->type;
      $file_name = 'recording_' . $user_id . '_';
      $file_name .= DB::next_record_id($file_name);
      $wav_file = $path_data . DIRECTORY_SEPARATOR . 'recording' . DIRECTORY_SEPARATOR . $file_name . '.wav';
    }

    foreach($aFile as $file) {
      $temporary_file = tempnam('/tmp', 'tmp_file') . '.wav';
      $this->create_wav($file_path, $file_type, $temporary_file);
      $aRecording[] = $temporary_file;
    }

    $this->concat_wav($aRecording, $wav_file);
    $this->file_name = $wav_file;
  }

  public function save()
  {
    $data = array(
        'recording_id' => $this->recording_id,
        'name' => $this->name,
        'file_name' => $this->file_name,
        'type' => $this->type,
        'description' => $this->description,
        'length' => $this->length,
        'codec' => $this->codec,
        'channel' => $this->channel,
        'sample' => $this->sample,
        'bitrate' => $this->bitrate
    );

    if (isset($data['recording_id']) && !empty($data['recording_id'])) {
      // update existing record
      $result = DB::update(self::$table, $data, 'recording_id');
      Corelog::log("Recording updated: $this->recording_id", Corelog::CRUD);
    } else {
      // add new
      $result = DB::update(self::$table, $data, false);
      $this->recording_id = $data['recording_id'];
      Corelog::log("New Recording created: $this->recording_id", Corelog::CRUD);
    }
    return $result;
  }

  private function concat_wav($inputRecording, $output_recording) {
    $sox_command = \ICT\Core\sys_which('sox', '/usr/bin');
    $recording_list = "'" . implode("' '", $inputRecording) . "'";
    $sox_cmd = $sox_command . " $recording_list '$output_recording'";
    Corelog::log("Concatenating recording list into single file", Corelog::CRUD, $sox_cmd);
    exec($sox_cmd);
  }

  private function create_wav($sourceFile, $type, $targetFile)
  {
    $this->length = 0;
    $sox_type = $this->recording_type_sox();
    //$allowed_type = $this->recording_type_allowed();
    // get installed sox version
    $raw_output = NULL;
    $sox_command = \ICT\Core\sys_which('sox', '/usr/bin');
    exec($sox_command . ' --version', $raw_output);
    $sox_output = isset($raw_output[0]) ? $raw_output[0] : 'v0.0.0'; // to avoid undefined index error
    $raw_version = NULL;
    preg_match('/v(\d+\.\d+\.\d+)/', $sox_output, $raw_version);
    $sox_version = isset($raw_version[1]) ? $raw_version[1] : '0.0.0'; // to avoid undefined index error

    if (in_array(strtolower($type), $sox_type)) {
      if (version_compare($sox_version, '13.0.0', '<')) {
        $sox_cmd = $sox_command . " '$sourceFile' -w -r 8000 -c 1 -s '$targetFile'";
      } else if (version_compare($sox_version, '14.3.0', '>')) {
        $sox_cmd = $sox_command . " '$sourceFile' -b 16 -r 8000 -c 1 -e signed-integer '$targetFile'";
      } else {
        $sox_cmd = $sox_command . " '$sourceFile' -2 -r 8000 -c 1 -s '$targetFile'";
      }
      Corelog::log("Converting source audio into pbx supported wav file", Corelog::CRUD, $sox_cmd);
      exec($sox_cmd);
    } else { // unsupported format, it is excepted that this will be never used
      //$fixedType = 'wav'; // assume wav
      $sox_cmd = \ICT\Core\sys_which('cp', '/usr/bin') . " '$sourceFile' '$targetFile'";
      Corelog::log("Converting unknown source audio into pbx supported wav file", Corelog::CRUD, $sox_cmd);
    }

    exec("chmod 644 '$targetFile'");

    if (version_compare($sox_version, '14.0.0', '<')) {
      $sox_cmd = $sox_command . " '$targetFile' -e stat 2>&1 | sed -n 's#^Length (seconds):[^0-9]*\\([0-9.]*\)$#\\1#p'";
    } else {
      $sox_cmd = $sox_command . " '$targetFile' -n stat 2>&1 | sed -n 's#^Length (seconds):[^0-9]*\\([0-9.]*\)$#\\1#p'";
    }
    $duration = NULL;
    exec($sox_cmd, $duration);
    Corelog::log("PBX supported wav file created", Corelog::CRUD, $duration);
    $this->length = ceil($duration[0]);
  }

  private function recording_type_allowed()
  {
    $type = array();
    $query = DB::query('codec', 'SELECT * FROM codec WHERE active=1');
    while ($codec = mysql_fetch_object($query)) {
      $type[$codec->codec_value] = $codec->codec_value;
    }
    return $type;
  }

  private function recording_type_sox()
  {
    // TODO get supported list of files from command line sox
    $type = array('8svx', 'aif', 'aifc', 'aiff', 'aiffc', 'al', 'amb', 'au', 'avr', 'caf', 'cdda', 'cdr', 'cvs',
        'cvsd', 'dat', 'dvms', 'f4', 'f8', 'fap', 'flac', 'fssd', 'gsm', 'hcom', 'htk', 'ima', 'ircam',
        'la', 'lpc', 'lpc10', 'lu', 'mat', 'mat4', 'mat5', 'maud', 'nist', 'ogg', 'paf', 'prc', 'pvf',
        'raw', 's1', 's2', 's3', 's4', 'sb', 'sd2', 'sds', 'sf', 'sl', 'smp', 'snd', 'sndfile', 'sndr',
        'sndt', 'sou', 'sox', 'sph', 'sw', 'txw', 'u1', 'u2', 'u3', 'u4', 'ub', 'ul', 'uw', 'vms', 'voc',
        'vorbis', 'vox', 'w64', 'wav', 'wavpcm', 'wv', 'wve', 'xa', 'xi');
    return $type;
  }

  public function get_link()
  {
    return $this->file_name;
  }

}
