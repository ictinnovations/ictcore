<?php
/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

class Email extends Service
{

  /** @const */
  const SERVICE_FLAG = 8;
  const SERVICE_TYPE = 'email';
  const CONTACT_FIELD = 'email';
  const MESSAGE_CLASS = 'Template';
  const GATEWAY_CLASS = 'Sendmail';

  public static function capabilities()
  {
    $capabilities = array();
    $capabilities['application'] = array(
        'email_receive',
        'email_send',
        'log'
    );
    $capabilities['account'] = array(
        'mailbox'
    );
    $capabilities['provider'] = array(
        'smtp',
        'sendmail'
    );
    return $capabilities;
  }

  public function application_template($application_name)
  {
    $gateway_class = static::GATEWAY_CLASS;
    $gateway_type = $gateway_class::GATEWAY_TYPE;
    switch ($application_name) {
      case 'email_send':
      case 'email_receive':
      case 'log':
        return "application/$application_name/$gateway_type/default.json";
    }
  }

}