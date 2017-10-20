<?php
//https://github.com/misterion/ko-process#do-not-use-composer
//require __DIR__ . '/vendor/autoload.php'; 
namespace ICT\Core;
//namespace misterion\Ko;
/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */
use Exception;
use ICT\Core\Contact;
use Cocur\BackgroundProcess\BackgroundProcess;
use dgr\nohup\Nohup;
use dgr\nohup\Process;
//use Firehed\ProcessControl;
//use misterion\ko-process\src\Ko\ProcessManager;
//use Ko\ProcessManager   ;
//use Ko\Process;

class Campaign
{
  /** @const */
  const COMPANY = -2;

  private static $table = 'campaign';
  private static  $trans_table = 'transmission';

  private static $primary_key = 'campaign_id';
  private static $fields = array(
      'campaign_id ',
      'program_id',
      'group_id',
      'delay',
      'try_allowed',
      'account_id',
      'status'
  );
  private static $read_only = array(
      'campaign_id'
  );
  /**
   * @property-read integer $campaign_id
   * @var integer
   */
  private $campaign_id = NULL;

  /** @var integer */
  public $program_id = NULL;

  /** @var integer */
  public $group_id = NULL;

  /** @var string */
  public $delay = NULL;

  /** @var string */
  public $try_allowed = NULL;

  /** @var integer */
  public $account_id = NULL;

  /** @var string */
  public $status = NULL;


  public function __construct($campaign_id = NULL)
  {
    if (!empty($campaign_id) && $campaign_id > 0) {
      $this->campaign_id = $campaign_id;
      $this->load();
    } else if (Campaign::COMPANY == $campaign_id) {
      $this->campaign_id = $campaign_id;
      $title = Conf::get('company:title', 'ICTCore');
      $aTitle = explode(' ', $title, 2);
      $this->program_id = $aTitle[0];
      $this->group_id = isset($aTitle[1]) ? $aTitle[1] : '';
    }
  }

  public static function construct_from_array($aCampaign)
  {
    $oCampaign = new Campaign();
    foreach ($aCampaign as $field => $value) {
      $oCampaign->$field = $value;
    }
    return $oCampaign;
  }

  public static function search($aFilter = array())
  {
    $aCampaign = array();
    $from_str = self::$table;
    $aWhere = array();
    foreach ($aFilter as $search_field => $search_value) {
      switch ($search_field) {
        case 'campaign_id':
          $aWhere[] = "$search_field = $search_value";
          break;
        case 'program_id':
          $aWhere[] = "$search_field LIKE '%$search_value'";
          break;
        case 'group_id':
          $aWhere[] = "$search_field = '$search_value'";
          break;
        case 'status':
          $aWhere[] = "$search_field LIKE '%$search_value%'";
          break;
      }
    }
    if (!empty($aWhere)) {
      $from_str .= ' WHERE ' . implode(' AND ', $aWhere);
    }

    $query = "SELECT * FROM " . $from_str;
    Corelog::log("Campaign search with $query", Corelog::DEBUG, array('aFilter' => $aFilter));
    $result = DB::query('Campaign', $query);
    while ($data = mysql_fetch_assoc($result)) {
      $aCampaign[$data['campaign_id']] = $data;
    }

    // if no Campaign found, check for special Campaigns
    if (empty($aCampaign) && isset($aFilter['campaign_id']) && $aFilter['campaign_id'] == Campaign::COMPANY) {
      $oCampaign = new Campaign($aFilter['campaign_id']);
      $aCampaign[$oCampaign->campaign_id] = array(
          'campaign_id' => $oCampaign->campaign_id,
          'program_id' => $oCampaign->program_id,
          'group_id' => $oCampaign->group_id,
          'status' => $oCampaign->status
         
      );
    }

    return $aCampaign;
  }

  private function load()
  {
    $query = "SELECT * FROM " . self::$table . " WHERE campaign_id='%campaign_id%' ";
    $result = DB::query(self::$table, $query, array('campaign_id' => $this->campaign_id), true);
    $data = mysql_fetch_assoc($result);
    if ($data) {
      $this->campaign_id = $data['campaign_id'];
      $this->program_id  = $data['program_id'];
      $this->group_id    = $data['group_id'];
      $this->delay       = $data['delay'];
      $this->try_allowed = $data['try_allowed'];
      $this->account_id  = $data['account_id'];
      $this->status      = $data['status'];
      Corelog::log("Campaign loaded id: $this->campaign_id $this->status", Corelog::CRUD);
    } else {
      throw new CoreException('404', 'Campaign not found');
    }
  }

  public function delete()
  {

     $this->task_cancel();
    Corelog::log("Campaign delete", Corelog::CRUD);
    return DB::delete(self::$table, 'campaign_id', $this->campaign_id, true);
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
    return $this->campaign_id;
  }
  /*public function email_to_phone()
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
  }*/
  public function save()
  {
    $data = array(
        'campaign_id' => $this->campaign_id,
        'program_id' => $this->program_id,
        'group_id' => $this->group_id,
        'delay' => $this->delay,
        'try_allowed' => $this->try_allowed,
        'account_id' => $this->account_id,
        'status' => $this->status
    );

    if (isset($data['campaign_id']) && !empty($data['campaign_id'])) {
      // update existing record
      $result = DB::update(self::$table, $data, 'campaign_id', true);
      Corelog::log("Campaign updated: $this->campaign_id", Corelog::CRUD);
    } else {
      // add new
     $act_id = $this->account_id;

     $result = DB::update(self::$table, $data, false, true);

     $this->campaign_id = $data['campaign_id'];
         
      $query = "SELECT c.first_name,c.last_name,c.phone,c.email,c.contact_id ,cl.contact_link_id,cl.contact_id ,cl.group_id FROM contact c INNER JOIN contact_link cl ON c.contact_id = cl.contact_id where cl.group_id=".$this->group_id." GROUP BY cl.contact_id";
      
      $result_contacts = mysql_query($query);


      while ($datacontact = mysql_fetch_assoc($result_contacts)) 
      {

        $aGroupcontact[$datacontact['contact_link_id']] = $datacontact;

      }
  
       foreach($aGroupcontact as $rowcontact)
      {
            $data_transmission = array(
            'title'            =>'bulk system',
            'account_id'       =>$act_id ,
            'contact_id'       =>$rowcontact['contact_id'],
            'origin'           =>'',
            'direction'         =>''
            );

           $oProgram = Program::load($this->program_id);

          $direction = empty($data_transmission['direction']) ? Transmission::OUTBOUND : $data_transmission['direction'];

          $oTransmission = $oProgram->transmission_create($rowcontact['contact_id'],$act_id , $direction);

         // $this->set($oTransmission, $data_transmission);

          if ($oTransmission->save()) 
          {
            $trans_id =  $oTransmission->transmission_id;
          } else 
          {
            throw new CoreException(417, 'Transmission creation failed');
          }
     }

      Corelog::log("New Campaign created: $this->campaign_id", Corelog::CRUD);
    }
    return $result;
  }
/*public function deamon($strng)
{
       echo $strng;
       echo  posix_getpid(); 
       echo "<br>";
       echo $this->campaign_id;
       $query = "UPDATE campaign set process_id=".posix_getpid() ."  where campaign_id =".$this->campaign_id;
      
       mysql_query($query);

         for ($i = 1; $i <=100; $i++)
        {
            // $result = $this->check($url);
            sleep(1);
            echo "count: ".$i."<br>";
            ob_flush(); flush();
        }
}*/
  public function start()
  {

    //echo dirname(__FILE__);

    exec(dirname(__FILE__)."/ex.php $this->campaign_id start", $output);
       //exec("/home/ictvision/Desktop/ictcore/core/ex.php $this->campaign_id start", $output);

      // print_r($output);
       echo $output[0];
  }
  public function stop()
  {
  
    exec(dirname(__FILE__)."/ex.php $this->campaign_id stop", $output);
    //print_r($output);
     echo $output[0];
     /*$query = "SELECT * FROM campaign where campaign_id=".$this->campaign_id;
      $result_campaign = mysql_query($query);
      $campaign_data = mysql_fetch_assoc($result_campaign);
     $p_id = $campaign_data['process_id'];
      echo "Process with : ".$p_id." has been Stoped";
      exec("kill -9 $p_id");*/
  }
  public function task_cancel()
  {
      $aSchedule = Schedule::search(array('type'=>'campaign', 'data'=>$this->campaign_id));

      foreach ($aSchedule as $schedule) 
      {
        $oSchedule = new Schedule($schedule['task_id']);
        $oSchedule->delete();

      }

  }
 
}
