<?php
/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

class Sms extends Service
{

  /** @const */
  const SERVICE_FLAG = 4;
  const CONTACT_FIELD = 'phone';
  const MESSAGE_CLASS = 'Text';
  const GATEWAY_CLASS = 'Kannel';

  public function capabilities()
  {
    return array(
        'log',
        'sms_send'
    );
  }

  public function template_application($application_name)
  {
    $cGateway = static::GATEWAY_CLASS;
    switch ($application_name) {
      case 'sms_send':
        $template = $cGateway::template_application($application_name, Sms::SERVICE_FLAG);
        break;
      default:
        $template = $cGateway::template_application($application_name);
        break;
    }
    return $template;
  }

}