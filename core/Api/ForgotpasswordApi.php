<?php

namespace ICT\Core\Api;

/* * ***************************************************************
 * Copyright © 2016 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\DB;
use ICT\Core\Api;
use ICT\Core\Conf;
use ICT\Core\User;
use ICT\Core\Forgot_password;
use Firebase\JWT\JWT;
use ICT\Core\Corelog;
use ICT\Core\CoreException;
use Firebase\JWT\ExpiredException;
use ICT\Core\Provider\Smtp;
use ICT\Core\Request;
use Swift_Attachment;
use Swift_Mailer;
use Swift_Message;
use Swift_SendmailTransport;
use Swift_SmtpTransport;

#[\AllowDynamicProperties]
class ForgotpasswordApi extends Api
{
  

  /**
   * Forgot password Api
   *
   * @noAuth
   * @url POST /forgot_password
   */
  public function forgot_password($data = array())
  {
    $result = new Forgot_password();
    return $result->forgot($data);
  }


  /**
   * Update password Api
   *
   * @noAuth
   * @url PUT /update_password
   */
  public function update_password($data = array())
  {
    $array = [
      'code'    => isset($data['code']) ? $data['code'] : null,
      'password' => isset($data['password']) ? $data['password'] : null,
    ];
    // Corelog::log('email ' . print_r($array, true), Corelog::INFO);
    $result = new Forgot_password();
    return $result->reset_password($array);
  }
}
