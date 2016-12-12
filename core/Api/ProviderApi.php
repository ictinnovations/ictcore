<?php

namespace ICT\Core\Api;

/* * ***************************************************************
 * Copyright © 2016 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Api;
use ICT\Core\CoreException;
use ICT\Core\Provider;

class ProviderApi extends Api
{

  /**
   * Create a new provider
   *
   * @url POST /provider/create
   */
  public function create($data = array())
  {
    $this->_authorize('provider_create');

    $oProvider = new Provider();
    $this->set($oProvider, $data);

    if ($oProvider->save()) {
      return $oProvider->provider_id;
    } else {
      throw new CoreException(417, 'Provider creation failed');
    }
  }

  /**
   * List all available providers
   *
   * @url GET /provider/list
   * @url POST /provider/list
   */
  public function list_view($data = array())
  {
    $this->_authorize('provider_list');
    return Provider::search($data);
  }

  /**
   * Gets the provider by id
   *
   * @url GET /provider/$provider_id
   */
  public function read($provider_id)
  {
    $this->_authorize('provider_read');

    $oProvider = new Provider($provider_id);
    return $oProvider;
  }

  /**
   * Update existing provider
   *
   * @url POST /provider/$provider_id/update
   * @url PUT /provider/$provider_id/update
   */
  public function update($provider_id, $data = array())
  {
    $this->_authorize('provider_update');

    $oProvider = new Provider($provider_id);
    $this->set($oProvider, $data);

    if ($oProvider->save()) {
      return $oProvider;
    } else {
      throw new CoreException(417, 'Provider update failed');
    }
  }

  /**
   * Create a new provider
   *
   * @url GET /provider/$provider_id/delete
   * @url DELETE /provider/$provider_id/delete
   */
  public function remove($provider_id)
  {
    $this->_authorize('provider_delete');

    $oProvider = new Provider($provider_id);

    $result = $oProvider->delete();
    if ($result) {
      return $result;
    } else {
      throw new CoreException(417, 'Provider delete failed');
    }
  }

}