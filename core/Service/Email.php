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

}