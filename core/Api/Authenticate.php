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
    // no _authorize needed

    $user_id = null;
    if (isset($data['email'])) {
      $user_id = $data['email'];
    } else if (isset($data['username'])) {
      $user_id = $data['username'];
    } else if (isset($data['user_id'])) {
      $user_id = $data['user_id'];
    } else {
      throw new CoreException(401, 'No valid username found');
    }

    $key_type = null;
    if (isset($data['password'])) {
      $key_type = 'password';
    } else if (isset($data['hash']) || isset($data['password_hash'])) {
      $key_type = 'password_hash';
    } else if (isset($data['cert']) || isset($data['certificate'])) {
      $key_type = 'certificate';
    } else if (!empty($_SERVER['REMOTE_ADDR'])) {
      $key_type = 'host';
      $data['host'] = $_SERVER['REMOTE_ADDR'];
    }
    if (!empty($key_type)) {
      $oUser = new User($user_id);
      $oUser->token = $oUser->email;
      if ($oUser->authenticate($data[$key_type], $key_type)) {
        return $oUser;
      }
    }

    throw new CoreException(401, 'Invalid user name and password');
  }

  /**
   * Cancel current authentication token
   *
   * @noAuth
   * @url POST /authenticate/cancel
   */
  public function cancel($data = array())
  {
    file_put_contents('/tmp/authenticate_cancel.data', $data);
    // no _authorize needed
    return true;
  }
}