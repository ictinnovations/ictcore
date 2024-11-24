<?php

namespace ICT\Core\Api\Account;

/* * ***************************************************************
 * Copyright © 2024 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Api\AccountApi;


class CidApi extends AccountApi
{

  /**
   * Create a new account
   *
   * @url POST /cids
   */
  
  public function create($data = array())
  {
    $data['type'] = 'cid';
    return parent::create($data);
  }


  /**
   * List all available accounts
   *
   * @url GET /cids
   */

  public function list_view($query = array())
  {
    $query['type'] = 'cid';
    return parent::list_view($query);
  }
}