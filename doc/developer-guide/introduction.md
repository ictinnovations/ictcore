Developer Guide for ICTCore
===========================
ICTCore is a Telecommunication PBX library a backend without any GUI, ICTCore support gateway connectivity, dialplan, extension, trunking and basic telecom services like call, email, sms and fax etc.. ICTCore goal is to be a common back-end for telecommunication related project. and it can be used to develop GUIs on top of it, to offer telecommunication solution and services.

Our main purpose to develop ICTCore is to have some application which can easily be extended and customized, so here in this guide we will try to explain that how much it is easy to develop and modify ICTCore as per new requirements. 


Directory Structure
===================
* core    : Actual package / Core libraries
* etc     : All configuration related files
* data    : Directory dedicated for user data
* bin     : File which are executable from command line
* wwwroot : to communicate with external world, with gateways and to server REST APIs
* log     : Activity Logs / log files
* db      : SQL scripts for new database initialization
* doc     : All type of documentation / guide
* .spec   : RPM spec files
* vendor  : Third party dependencies and libraries mannaged by Composer
* composer.json
* CHANGLOG.md
* TODO.md
* LICENSE.md
* README.md

### ICTCore Design
For ease of understanding beside common functions we can divided ICTCore design into four major sections as mentioned in following, It will help you to understand that what section you need to address if you want to extend / add new functionality. following is very brief introduction of each section, i.e just providing a list. for complete detail please check related directory in documentation directory.

Logic
-----
* Program     : Contains complete logic for a particular job
* Application : Define logic of a single communication step 
* Action      : Define a relation between two application

User data
---------
All user data, user info, contacts, uploaded files, received messages and documents will be saved into data folder in respective directories as per message type. currently there are following data types are available

* Account : Account will hold users info
* Contact : Contact will hold contacts info
* Message : Message is responsible to store data which is being communicated

Operation
---------
This section hold classes / functions for commons stages / steps which happen during life cycle of a single telecommunication event. In other words ICTCore has to go all of the following whenever there is any kind of telecommunication, regardless its type.

* Transmission : Represent a whole event of communication
* Spool        : Each attempt to transmit message will be handled in spool
* Result       : Its responsibility is to collect data / result during communication
* Schedule     : In case we need to delay, schedule a telecommunication event / transmission
* Gateway      : Gateway is a medium and our end point to deliver and receive communication
* Service      : Helper class / functions for common telecommunication related activities

Infrastructure
--------------
This section address telecommunication resources and setup which can be utilized by ICTCore

* Provider : trunk management interface
* Exchange : TBD, Route management interface
* Node     : TBD, Load balancing and fail-over interface

common
------
Common code / functions ( not related to telecommunication ) which are required to perform basic system and OS related activities.

* Token    : to change communication parameters dynamically with variable support
* core.php : main include file, with common ict related functions
* lib      : Common programming functions like database, configuration etc ..


Database
--------
Our database design should be suitable for standalone functionality and execution, as we don't wan't to be depended to any Client applications / GUI. In this regard we will save every thing related to telecommunication in project's database. following are a list of common components. for other components please check documentation of related class / object.

1. Users
2. Roles and permissions
3. User activity logs
4. System Configurations

