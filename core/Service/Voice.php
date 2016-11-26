<?php
/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

class Voice extends Service
{

  /** @const */
  const SERVICE_FLAG = 1;
  const SERVICE_TYPE = 'voice';
  const CONTACT_FIELD = 'phone';
  const MESSAGE_CLASS = 'Recording';
  const GATEWAY_CLASS = 'Freeswitch';

}