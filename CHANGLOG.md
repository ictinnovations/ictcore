ICTCore 1.2.0
-------------

Dated: 27-Aug-2021

* Bugs removal
* Configurations added
* Freeswitch upgraded to Freeswitch 1.20
* Dialing prefix manipulation support added
* Api guide rewritten into blueprint api specifications
* Provisioning API added for phones / extensions
* API added for System statistic


ICTCore 1.1.0
-------------

Dated: 6-Jan-2021

* Cover page support in both web2fax and email2fax
* Added the support for a single field like phone number in csv file

ICTCore 1.0.2
-------------

Dated: 13-May-2020

* Voice TTS application and program added
* Coversheet support added into Emailtofax prorgram
* Fax Quality support added
* IVR program added
* Data auditing and filtration support added in APIs
* HTTP protocol support and Ufone sms gateway support added
* DID and Forwarding support added for Fax ATA support
* File formate supported added for major Office related formats
* Multiple documents/attachments support added for fax
* Performance and installation related improvements

ICTCore 0.8.0
-------------

Dated: 21-December-2017

* Campaign support and APIs added
* Call Transfer application and Agent program added
* Authentication improved, JWT support added
* Media related APIs improved for file upload / download support.
* Rest URL structure improved as per REST standards / recommendations
* CORS (cross origin) support added

ICTCore 0.7.6
--------------

Dated: 06-October-2017

* Session support added
* Bug fixes

ICTCore 0.7.5
--------------

Dated: 07-March-2017

* Extension support added for accounts
* Twig based template added for gateway configurations and Application data
* Data and Token libraries updated
* Sip, SMTP and SMPP added as provider sub-type
* Multi tasking support improved for Task and Schedule
* Namespaces and PSR-4 based auto-loading support added
* PhpUnit support added for unit testing

ICTCore 0.7.0
--------------

Dated: 20-September-2016

* Refactoring, logic and flow and api refactoring

ICTCore 0.6.0
-------------

Dated: 29-June-2016

* Rest interface and APIs development
* User guide for REST APIs
* User authentication and authorization support added
* Proprietary license replaced with MPLv2
* Code compilation removed, to make it open source
* CentOs 7 support added

ICTCore 0.5.0
-------------

Dated: 19-March-2015

 * Dynamic account selection / authentication support added for new incoming requests
 * Schedule support added for transmissions
 * New applications and programs created for SMS Receive and SMS Send
 * New functions to associate and disassociate Accounts with programs
 * Developer guide improved
 * Logging interface improved

ICTCore 0.4.0
-------------

Dated: 19-March-2015

 * Program and Application support added
 * New applications created
   * Originate
   * Connect
   * Fax send
   * fax receive
   * Playback
   * Disconnect
   * Inbound
   * Log
   * Email receive 
   * Email send
 * New programs created
   * Sendfax
   * Voice message
   * Receive Fax
   * Email to fax
   * Fax to Email
 * Application result saving support added
 * Obsolete code has been cleared

  
ICTCore 0.3.0
-------------

Dated: 23-February-2015

 * Documentation improved with sequence and flow diagrams
 * Spec file created for ICTCore RPM
 * DID will be served by Account, did table replaced with account


ICTCore 0.2.0
-------------

Dated: 11-September-2014

 * Provider interface created
 * Spool interface created
 * Transmission interface created
 * Contact interface created
 * Account interface created
 * Token class improved
 * Database improved
 * Services interface improved
 * Gateway interface improved
 * Libraries improved
 * Develop fax send, receive and email send receive interfaces
 * Configuration improved for database, freeswitch and email


ICTCore 0.1.0
-------------

Dated: 17-March-2014

 * Starting a new project ICTCore, it will cover all telecommunication / ict related backend activities
 * Basic structure for Gateway, Message and Service
 * Freeswitch Gateway created
 * Sendmail Gateway created
 * Fax Service created
 * Email Service created
 * Document Message object created
 * Template Message object created
