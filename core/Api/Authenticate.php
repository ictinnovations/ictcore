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
use ICT\Core\User;

class AuthenticateApi extends Api
{

  /**
   * Gets the user after authenticating provided credentials
   *
   * @noAuth
   * @url POST /authenticate
   */
  public function create($data = array())
  {

    $key_type = null;
    $credentials = null;

    if (isset($data['hash'])) {
      $key_type = User::AUTH_TYPE_DIGEST;
      $credentials = array('username' => null, 'password' => $data['hash']);
    } else if (isset($data['password_hash'])) {
      $key_type = User::AUTH_TYPE_DIGEST;
      $credentials = array('username' => null, 'password' => $data['password_hash']);
    } else {
      $key_type = User::AUTH_TYPE_BASIC;
      $credentials = array('username' => null, 'password' => $data['password']);
    }

    if (isset($data['email'])) {
      $credentials['username'] = $data['email'];
    } else if (isset($data['username'])) {
      $credentials['username'] = $data['username'];
    } else if (isset($data['user_id'])) {
      $credentials['username'] = $data['user_id'];
    } else {
      throw new CoreException(401, 'No valid username found');
    }

    try {
      $oUser = User::authenticate($credentials, $key_type);
      $oUser->token = $oUser->generate_token();
      $oUser->access_token = $oUser->token;
      $oUser->expires_in = (60 * 60 * 24 * 30 * 12 * 1); // valid for one year
      $oUser->token_type = 'Bearer';
      $oUser->scope = 'All';

      return $oUser;
    } catch (CoreException $ex) {
      throw new CoreException(401, 'Invalid user name and password: '.$ex->getMessage());
    }
  }

  /**
   * Cancel current authentication token
   *
   * @noAuth
   * @url POST /authenticate/cancel
   */
  public function cancel()
  {
    // no _authorize needed
    // and nothing to do
    return true;
  }
}
