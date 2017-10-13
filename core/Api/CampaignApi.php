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
use ICT\Core\CoreException;

class CampaignApi extends Api
{

  /**
   * Create a new Campaign
   *
   * @url POST /campaign/create
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
   * @url GET /campaign/list
   * @url POST /campaign/list
   */
  public function list_view($data = array())
  {
    $this->_authorize('campaign_list');
    return Campaign::search($data);
  }

  /**
   * Gets the campaign by id
   *
   * @url GET /campaign/$contact_id
   */
  public function read($contact_id)
  {
    $this->_authorize('campaign_read');

    $oCampaign = new Campaign($Campaign_id);
    return $oCampaign;
  }

  /**
   * Update existing campaign
   *
   * @url POST /campaign/$campaign_id/update
   * @url PUT /campaign/$campaign_id/update
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
   * @url GET /campaign/$campaign_id/delete
   * @url DELETE /campaign/$campaign_id/delete
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
       $string = 'start';

      $oCampaign= new Campaign($campaign_id);
         
        $result = $oCampaign->check($string);



    }


     /**
   * stop campaign
   *
   * @url GET /campaign/$campaign_id/stop
   * @url POST /campaign/$campaign_id/stop
   */
    
    public function stop_campaign($campaign_id)
    {
     // echo $campaign_id;
      $oCampaign= new Campaign($campaign_id);
          
        $result = $oCampaign->check('stop');

    }
}