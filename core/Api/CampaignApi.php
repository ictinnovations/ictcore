<?php

namespace ICT\Core\Api;

/* * ***************************************************************
 * Copyright © 2017 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */
use ICT\Core\Api;
use ICT\Core\Campaign;
use ICT\Core\Schedule;
use ICT\Core\Transmission;
use ICT\Core\CoreException;

class CampaignApi extends Api
{
  /**
   * Create a new Campaign
   *
   * @url POST /campaigns
   */
  public function create($data = array())
  {
    $this->_authorize('campaign_create');
    $oCampaign = new Campaign();
    $this->set($oCampaign, $data);

    if ($oCampaign->save()) {
      return $oCampaign->campaign_id;
    } else {
      throw new CoreException(417, 'Campaign creation failed');
    }
  }
  /**
   * List all available contacts
   *
   * @url GET /campaigns
   */
  public function list_view($query = array())
  {
    $this->_authorize('campaign_list');
    return Campaign::search((array)$query);
  }
  /**
   * Gets the campaign by id
   *
   * @url GET /campaigns/$campaign_id
   */
  public function read($campaign_id)
  {
    $this->_authorize('campaign_read');
  //  echo $Campaign_id;
    $oCampaign = new Campaign($campaign_id);
    return $oCampaign;
  }
  /**
   * Update existing campaign
   *
   * @url PUT /campaigns/$campaign_id
   */
  public function update($campaign_id, $data = array())
  {
    $this->_authorize('campaign_update');
    $oCampaign= new Campaign($campaign_id);
    $this->set($oCampaign, $data);

    if ($oCampaign->save()) {
      return $oCampaign;
    } else {
      throw new CoreException(417, 'Campaign update failed');
    }
  }
  /**
   * Remove contact
   *
   * @url DELETE /campaigns/$campaign_id
   */
  public function remove($campaign_id)
  {
    $this->_authorize('campaign_delete');
    $oCampaign= new Campaign($campaign_id);
    $result = $oCampaign->delete();

    if ($result) {
      return $result;
    } else {
      throw new CoreException(417, 'Campaign delete failed');
    }
  }
   /**
   * start campaign
   *
   * @url GET /campaign/$campaign_id/start
   * @url POST /campaign/$campaign_id/start
   */
    public function start_campaign($campaign_id)
    {
       $this->_authorize('campaign_start');
       $oCampaign= new Campaign($campaign_id);
       $result = $oCampaign->start();
       return $result ;
    }
     /**
   * stop campaign
   *
   * @url GET /campaign/$campaign_id/stop
   * @url POST /campaign/$campaign_id/stop
   */
    public function stop_campaign($campaign_id)
    {
        $this->_authorize('campaign_stop');
        $oCampaign= new Campaign($campaign_id);
        $result = $oCampaign->stop();
        return $result ;
    }
    /**
   * Schedule Campaign 
   *
   * @url PUT /campaign/$campaign_id/$action/schedule
   * @url POST /campaign/$campaign_id/$action/schedule
   */
  public function schedule_create($campaign_id,$action,$data = array())
  {
    $this->_authorize('task_create');
    $oSchedule = new Schedule();
    $this->set($oSchedule, $data);
    $oCampaign = new Campaign($campaign_id);
    $oSchedule->type = 'campaign';
    $oSchedule->action = $action ;
    $oSchedule->data = $oCampaign->campaign_id;
    $oSchedule->account_id = $oCampaign->account_id;
    $oSchedule->save();
    return $oSchedule->task_id;
  }
  /**
   * Cancel Campaing schedule
   *
   * @url DELETE /campaign/$campaign_id/schedule/cancel
   */
  public function schedule_cancel($campaign_id,$data = array())
  {
    $oCampaign = new Campaign($campaign_id);
    return $oCampaign->task_cancel();
  }

}