Basic components of ICTCore
===========================
Before starting development with ICTCore, it is recommended to get an introduction with basic building block of ICTCore. for ease of understanding, we can divide them into four main categories as mentioned in following.

1. User Data
2. Logic
3. Operation
4. Infrastructure


User Data
---------
ICTCore store all user data in such a way that it can be reused over and over again. further it tries to generalize user data so it can be used by different type of services and scenarios.

### Account
Account will hold users/client contact information for all type of services like phone number for voice and fax, email for email etc... Each user can have one or multiple account, think it like DIDs or extension. account can be used as source for outbound, and as destination for inbound communication. 

### Contact
Just like account, but in opposite direction i.e it will be source in inbound, and destination in outbound transmissions. user can create as many contact as he need, further contact can be created dynamically for new destination while creating transmission.

### Message
Stored data / file holding information received or need to be delivered via some telecommunication related activity, for example Voice recording or Fax document etc..

1. Document (for fax)
2. Template (for email)
3. Recording (for voice)
4. Text (for sms)


Logic
-----
In ICTCore we have tried to keep logic related components independent and separate from other technical things. there are three components in this category.

1. Program
2. Application
3. Action

### Program
A Program is like a scenario which can facilitate some certain requirement from real world telecommunication needs. technically it is an arrangement of basic communication related activities (applications) to achieve a specific target, program classes are required to perform following functionalities.

* Prepare / compile and setup a program instance with custom setting and messages
* Create new transmission
* Process incoming transmission
* Execute outgoing transmission
* Process application results after execution of each application

following a list of some sample programs

1. Email2FAX
2. AutoAttendent
3. FollowMe
4. Invite to Conference
5. etc ...

### Application
Applications are also telecommunication related activities but at very basic level, they are supposed to do only a single task per application. application classes are required to perfom following functionalities.

* Prepare / compile and setup an application instance (optional)
* Execute application
* Process application results and determine next application to execute

following are some sample application

1. Dial
2. Answer
3. SendSms
4. SendEmail
5. Transfer
6. Hangup etc ...

### Action
A condition based link between two applications, will determinate which application will be executed after current application based on the result of previous application.


Operation
---------
It is a set of common steps which are required to be performed on each communication event, contradictory to logic part it is not supposed to change for new scenarios.

### Transmission
A Transmission is a single communication event, like a call or email delivery. it store all major status regarding that particular communication event like success or failure, it hold the final results regardless how many attempts are made to achieve that.

### Spool
A sub object for transmission, we can say it represent and hold method related to a single attempt of transmission.

### Service
It is a library of common functions which ICTCore needs to perform various things. Normally function defined in Service classes are those which need to produce different results depending on selected communication channel. so there are separate classes for each type of service as mentioned in following.

1. Fax
2. Email
3. Voice
4. Sms

### Result
During the course of transmission / program if some user response or application result is important, then it will be permanently saved into database using Result interface.

### Gateway
Telecommunication engine / backend like Asterisk, Freeswitch, Sendmail etc .. currently there are three gateway available int ICTCore 

1. Freeswitch
2. Sendmail
3. Kannel

### Task and Schedule
In case a transmission or some other action require a delay execution, Task and Schedule interfaces can be used for that. For example a reminder or failed transmission can be configured for retry, by using Schedule interface.


Infrastructure
--------------
Currently we only support Providers but later on we will add other components.

1. Provider
2. (TODO) Routes
3. (TODO) Nodes
4. (TODO) Load balancers
4. (TODO) Failover node
5. (TODO) Backup servers

### Provider
A trunk, 3rd party provider required to originate / terminate transmissions. ICTCore support following 3 type providers

1. Sip for Voice and Fax
2. Smpp for SMS
3. Sendmail for Email
4. Emailcmd for Email
5. Http for SMS
