<?php
/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

class Fax extends Service
{

  /** @const */
  const SERVICE_FLAG = 2;
  const CONTACT_FIELD = 'phone';
  const MESSAGE_CLASS = 'Document';
  const GATEWAY_CLASS = 'Freeswitch';

  public function capabilities()
  {
    return array(
        'log',
        'originate',
        'connect',
        'fax_send',
        'fax_receive',
        'disconnect'
    );
  }

  public function template_application($application_name)
  {
    $cGateway = static::GATEWAY_CLASS;
    switch ($application_name) {
      case 'originate':
        $template = $cGateway::template_application($application_name, Fax::SERVICE_FLAG);
        break;
      default:
        $template = $cGateway::template_application($application_name);
        break;
    }
    return $template;
  }

}