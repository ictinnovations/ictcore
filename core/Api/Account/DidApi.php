<?php

namespace ICT\Core\Api\Account;

/* * ***************************************************************
 * Copyright © 2016 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Api\AccountApi;

class DidApi extends AccountApi
{

  /**
   * Create a new account
   *
   * @url POST /dids
   */
public function create($data = array(), $account_id = null)
  {
    $data['type'] = 'did';
    return parent::create($data);
  }
  
  /**
   * List all available accounts
   *
   * @url GET /dids
   */
  public function list_view($query = array())
  {
    $query['type'] = 'did';
    return parent::list_view($query);
  }

}
