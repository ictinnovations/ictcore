System Dependencies
===================
* Linux  (GPL)
* Freeswitch (MPL 1.1 License)
* Sendmail (Sendmail License)
* Kannel (BSD-style)
* PHP (BSD)
* Apache (Apache License 2.0)
* MySQL (GPL) or MariaDB (LGPL)

* composer (MIT)
    * twig (BSD)
    * jacwright (MIT)
    * nategood (MIT)
    * swiftmailer (MIT)
    * aza/thread (MIT)


Class Dependencies
==================

### Common Libraries
* Conf
* Data
* DB
* Log
* Scheme
* Session
* Thread
* Token

### Core (Communication Related)
* Request
* Response
* Dialplan
* Program
    * Message / Data
    * Application
        * Gateway
        * Action
        * Dialplan (for inbound programs only)
    * Account
* Transmission
    * Account
    * Contact
    * Spool
    * Result
* Service
    * Gateway