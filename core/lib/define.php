<?php
/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

/* constant to represent all nodes simultaneously */
define('NODE_ALL', 0);

/* Server types, usefull in scalibity */
define('SERVER_FILE', 1);
define('SERVER_DATABASE', 2);
define('SERVER_API', 4);
define('SERVER_WEB', 8);
define('SERVER_CAMPAIGN', 16); // ALSO serve as load balance server
define('SERVER_GATEWAY', 32);

/* List of Services available in system */
define('SERVICE_VOICE', 1);      // TODO
define('SERVICE_FAX', 2);
define('SERVICE_SMS', 4);      // TODO
define('SERVICE_EMAIL', 8);
define('SERVICE_VIDEO', 16);      // TODO
define('SERVICE_POST', 32);      // TODO for letter printing

/* Order List, can be used as weight to execute application in certain order */
define('ORDER_PRE', 1);
define('ORDER_PERI', 2); // Durring, middle
define('ORDER_POST', 4);
define('ORDER_INIT', 8); // Dial / Ring
define('ORDER_CONNECT', 16); // Answer
define('ORDER_START', 32); // Greetings
define('ORDER_ACTIVE', 64); // Call
define('ORDER_END', 128); // Bye
define('ORDER_CLOSE', 256); // Hangup

/* List of Gateways */
define('GATEWAY_CORE', 0); // == NODE ALL or ALL SERVERS
define('GATEWAY_ASTERISK', 1); // TODO
define('GATEWAY_KANNEL', 2); // TODO
define('GATEWAY_SENDMAIL', 4);
define('GATEWAY_FREESWITCH', 8);

define('PERMISSION_NONE', 0);
define('PERMISSION_CURRENT', 1); // permission for current acting user/account
define('PERMISSION_ORIGINAL', 2); // permission for user/account which used during login
define('PERMISSION_ALL', 3);

// following constants are being used in conf.php
// **********************************************
define('CONF_DEFAULT', -1);
define('CONF_ALL', 0);
define('CONF_SYSTEM', 1);
define('CONF_USER', 2);
define('CONF_CAMPAIGN', 3);
define('CONF_CONTACT', 4);  // not in use, only for concept

define('CONF_PERMISSION_GLOBAL_WRITE', 1);
define('CONF_PERMISSION_GLOBAL_READ', 2);
define('CONF_PERMISSION_NODE_WRITE', 4);
define('CONF_PERMISSION_NODE_READ', 8);
define('CONF_PERMISSION_ADMIN_WRITE', 16);
define('CONF_PERMISSION_ADMIN_READ', 32);
define('CONF_PERMISSION_USER_WRITE', 64);
define('CONF_PERMISSION_USER_READ', 128);

// system = 170 = GLOBAL_READ | NODE_READ | ADMIN_READ | USER_READ
// admin  = 186 = system | ADMIN_WRITE
// user   = 250 = admin | USER_WRITE