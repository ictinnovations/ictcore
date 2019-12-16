Introduction to Settings available for ICTCore
==============================================

Program related Configuration
-----------------------------

### Emailtofax settings
Following settings are available for emailtofax program

 * emailtofax_coversheet:  
If user want to use email body as Cover-Sheet to outgoing fax then setting should have **body** as value.


System Configuration
--------------------
System wide settings can be controlled from `/usr/ictcore/etc/ictcore.conf` file. Importated sections are listed below

### Company settings
Compnay settings will be used as default account while communicating on the behalf of system. for example company email will be used while setting system generated notifications.

 * name
 * email
 * phone
 * address

### Website settings
Settings for the web and API interface of the ICTCore

 * title:  
Website title

 * host:  
Domain name assocated with ICTCore installation or system IP Address (without any additional charector)

 * port:  
Default Web Port

 * path:  
Sub address / URL path for API interface

 * url:  
Complete URL for APIs, along with protocols prefix

 * log:  
Log settings for ICTCore, a space separated combination can be used, from the following values
    * error
    * warning
    * notice
    * info
    * crud: log object creation, change and delete events
    * logic: log decesion made at program and application level
    * flow: log application flow
    * common
    * debug
    * auth  
    Even following are not valid log modes, bug using these will trigger ictcore to create detailed log entries
    * extra: add extra data to log entries
    * trace: add execution trace in log entries
 * cookie
 * cors: CORS settings to restrict APIs use only to valid users

### Provisioning settings
Settings related to sip/web phone

 * host: domain name or ip address of the sip server
 * port: sip port
 * wss: wss port default is 7443

### Database (db) settings
 * host
 * port
 * user
 * pass
 * name
 * type

