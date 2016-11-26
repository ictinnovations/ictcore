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
  const SERVICE_TYPE = 'sms';
  const CONTACT_FIELD = 'phone';
  const MESSAGE_CLASS = 'Text';
  const GATEWAY_CLASS = 'Kannel';

}