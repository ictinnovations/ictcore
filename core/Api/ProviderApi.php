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
   * @url POST /providers
   */
  public function create($data = array())
  {
    $this->_authorize('provider_create');
    $type = $data['type'];
    unset($data['type']);
    $oProvider = Provider::load($type);
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
   * @url GET /providers
   */
  public function list_view($query = array())
  {
    $this->_authorize('provider_list');
    return Provider::search((array)$query);
  }

  /**
   * Gets the provider by id
   *
   * @url GET /providers/$provider_id
   */
  public function read($provider_id)
  {
    $this->_authorize('provider_read');
    $oProvider = Provider::load($provider_id);
    return $oProvider;
  }

  /**
   * Update existing provider
   *
   * @url PUT /providers/$provider_id
   */
  public function update($provider_id, $data = array())
  {
    $this->_authorize('provider_update');
    $oProvider = Provider::load($provider_id);
    $oProvider->name = $data['name'];
    $oProvider->service_flag = $data['service_flag'];
    $oProvider->node_id = $data['node_id'];
    if ($oProvider->save()) {
      return $oProvider;
    } else {
      throw new CoreException(417, 'Provider update failed');
    }
  }

  /**
   * Create a new provider
   *
   * @url DELETE /providers/$provider_id
   */
  public function remove($provider_id)
  {
    $this->_authorize('provider_delete');
    $oProvider = Provider::load($provider_id);
    $result = $oProvider->delete();
    if ($result) {
      return $result;
    } else {
      throw new CoreException(417, 'Provider delete failed');
    }
  }

}