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
  const CONTACT_FIELD = 'email';
  const MESSAGE_CLASS = 'Template';
  const GATEWAY_CLASS = 'Sendmail';

  public function capabilities()
  {
    return array(
        'log',
        'email_send'
    );
  }

  public function template_application($application_name)
  {
    $cGateway = static::GATEWAY_CLASS;
    switch ($application_name) {
      case 'email_send':
        $template = $cGateway::template_application($application_name, Email::SERVICE_FLAG);
        break;
      default:
        $template = $cGateway::template_application($application_name);
        break;
    }
    return $template;
  }

}