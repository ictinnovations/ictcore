Unified Communication
=====================
 * Multiple services are supported like
   * Voice / call
   * Fax
   * Sms
   * Email
 * Single interface to access all services
 * Unified data structure ( ie. contact, account and results etc..) for all type of services
 * Different services can be triggered at same time in response of a single event
 * Ability to link services to each other based on condition (result of previous service)

Ease of use
===========
 * Automated installation via RPM
 * No need to bother with gateway installation and configuration
 * Rest API interface
 * No need to learn and remember complex telecommunication terms
 * Create provider (trunk / termination) via API call
 * Create account (DID / extensions) via API call

### For User
A user just need to understand following, before he/she can fully utilize the power of each unified communication
 * Contact
 * Message
 * Program
 * Transmission
 * And the use of REST APIs

### For Admin
In addition to user, admin may also need to understand following
 * Provider
 * Account
 * Program
 * Application
 * Action

Flexibility
===========
  * ICTCore build for custom scenarios
  * Application combined with condition can make dynamic scenario possible
  * Multiple application can be started in respone of one application
  * Data saved / recorded during communication will be available to use in upcomming applications
  * Account, contact, message can be given as argument, so no need to change will swaping user data.

Easy to develop
===============
  * Developed with PHP 5, MySQL and Linux (LAMP)
  * Infrastructure, Operation and logic separation
  * With extendable structures and classes it is very easy to develop new
    * Services
    * Gateways
    * Applications
    * Programs
