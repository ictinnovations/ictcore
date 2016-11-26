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
  const SERVICE_TYPE = 'fax';
  const CONTACT_FIELD = 'phone';
  const MESSAGE_CLASS = 'Document';
  const GATEWAY_CLASS = 'Freeswitch';

}