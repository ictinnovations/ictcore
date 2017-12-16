ICTCore REST APIs Guide
=======================

Overview
--------
* __API Endpoint__ : Domain / web url corresponding address for ictcore/wwwroot installation directory.
* __API Access__ : Any valid combination of username and password created in usr table.
* __POST Method__: Any data submitted to POST method based APIs must be encoded as json.
* __DELETE Method__: user can use both DELETE and GET methods for delete APIs.
* __List APIs__: All list APIs support optional search filter, to search user need to use search parameters in url as query string using key value pair.

### HTTP Response Code
* __200__	Function successfully executed.
* __401__ Invalid or missing username or password.
* __403__	Permission denied. User is not permitted to perform requested action.
* __404__	Invalid API location or request to none existing resource. Check the URL and posting method (GET/POST).
* __412__ Data validation failed.
* __417__ Unexpected error.
* __423__ System is not ready to perform requested action.
* __500__	Internal server error. Try again at a later time.
* __501__	Feature not implemented.

Please note that all APIs requires authentication, you would need to send username and password as HTTP authentication header. See the cURL examples below for more information on how to do this.

Examples
--------
cURL

### Simple request
GET request with no arguments. ( but with http authentication )
```shell
curl -user myuser:mysecret "https://core.example.com/accounts"
```

Another GET request to list all recordings
```shell
curl -user myuser:mysecret "https://core.example.com/recordings"
```

### 1. Creating message and program
__1.1 Creating an Email message__  
POST request with additional parameters. (NOTE: all post request must be encoded as json)  
create a data.json file as following
```json
{
    "name": "sampleEmail",
    "subject": "Hello World!",
    "body": "<h1> hello HTML! </h1>",
    "body_alt": "hello text!",
}
```
then use this file with curl request
```shell
curl -user myuser:mysecret -H "Content-Type: application/json" -X POST -d @data.json "https://core.example.com/messages/templates"
```
In response to this request ICTCore will return template_id, we will use this id while creating program

__1.2 Creating a Send Email Program__  
POST request to create a program, in POST data we have provide template_id from recently created template along with account_id
create a data.json file as following
```json
{
    "account_id": "12",
    "template_id": "23",
}
```
then use this file with curl request
```shell
curl -user myuser:mysecret -H "Content-Type: application/json" -X POST -d @data.json "https://core.example.com/programs/sendemail"
}
```
In response to this request ICTCore will return program_id, we will use this id while creating new transmission

### 2. Creating contact and dialing it
__2.1 Creating a Contact__
POST email address and other details to create new contact
create a data.json file as following
```json
{
    "first_name": "Test",
    "last_name": "Contact",
    "phone": "1111111111",
    "email": "user@domain.com",
}
```
then use this file with curl request
```shell
curl -user myuser:mysecret -H "Content-Type: application/json" -X POST -d @data.json "https://core.example.com/contacts"
}
```
In response to this request ICTCore will return contact_id for newly created record

__2.2 Creating a Transmission__  
POST initiate a new transmission based on existing send email program and recently created contact
create a data.json file as following
```json
{
    "contact_id": "33",
}
```
```shell
curl -user myuser:mysecret -H "Content-Type: application/json" -X POST -d @data.json "https://core.example.com/programs/124/transmissions"
}
```
In response to this request ICTCore will return newly created transmission_id

__2.3 Sending Transmission__  
GET request ICTCore to send / dial previously saved transmission  
```shell
curl -user myuser:mysecret -H "Content-Type: application/json" -X POST -d @data.json "https://core.example.com/transmissions/144/send"
}
```


Authentication
==============
### POST authenticate
Create and return authentication token / session key.

* __Parameters__  
An associative array containing key and value pairs based on following fields
```json
{
    "username": "__String__",
    "password": "__String__"
}

Note: Unlike other APIs this API does not require separate authentication in header

### POST authenticate/cancel
Cancel an existing authentications token / session

Contact / pre defined destination number
========================================
### POST contacts
Create new contact

* __Parameters__  
An associative array containing key and value pairs based on following fields
```json
{
    "first_name": "__String__",
    "last_name": "__Optional_String__",
    "phone": "__Digits__",
    "email": "__Email__",
    "address": "__Optional_String__",
    "custom1": "__Optional_String__",
    "custom2": "__Optional_String__",
    "custom3": "__Optional_String__",
    "description": "__Optional_String__",
}
```
* __Response__  
__contact_id__ of created contact record

### GET contacts
list all exiting contacts, optionally client can filter contacts using query string (key value pair) in url, while using any of following fields
```
  first_name: __String__
  last_name: __String__
  phone: __Digits__
  email: __Email__
```

* __Response__  
an array of contacts indexed on contact_id

### GET contacts/{contact_id}
Read / view complete contact data

* __Parameters__  
Replace {contact_id} in url with valid contact_id

* __Response__  
Contact details in associative array

### PUT contacts/{contact_id}
Update an existing contact

* __Parameters__  
Replace {contact_id} in url with valid contact_id, fields require modifications will be POSTed in same way as `contacts`

* __Response__  
Return updated contact data as an associative array

### DELETE contacts/{contact_id}
Delete an existing contact

* __Parameters__  
Replace {contact_id} in url with valid contact_id

Message / pre defined information to be send
============================================

Fax Documents
-------------
### POST messages/documents
Create new document

* __Parameters__  
A json encoded associative array containing key and value pairs based on following fields
```json
{
    "name": "__String__",
    "description": "__Optional_String__",
}
```
* __Response__  
__document_id__ of recently created document record

* __Note__  
Media / document will be uploaded separately using PUT messages/documents/{document_id}/media

### GET messages/documents
list all exiting documents, optionally client can filter documents using query string (key value pair) in url, while using any of following fields
```
  name: __String__
  type: __String__, three digit file extension representing file type
  description: __String__
```

* __Response__  
an array of documents indexed on document_id

* __Note__  
Media / document can be downloaded separately using GET messages/documents/{document_id}/media

### GET messages/documents/{document_id}
Read / view complete document data

* __Parameters__  
Replace {document_id} in url with valid document_id

* __Response__  
document details in associative array

### PUT messages/documents/{document_id}
Update an existing document

* __Parameters__  
Replace {document_id} in url with valid document_id, fields require modifications will be POSTed in same way as `documents`

* __Response__  
Return updated document data as an associative array

### DELETE messages/documents/{document_id}
Delete an existing document

* __Parameters__  
Replace {document_id} in url with valid document_id

### PUT messages/documents/{document_id}/media
Upload media / pdf file for an existing document, this method should be called followed by POST messages/documents

* __URL Parameters__  
Replace {document_id} in url with valid document_id

* __POST Parameters__  
File contents and related headers

* __Response__  
document_id of updated record

### GET messages/documents/{document_id}/media
Download media / pdf file for an existing document

* __Parameters__  
Replace {document_id} in url with valid document_id

* __Response__  
Media / Pdf file download will be started

Voice Recordings
----------------
### POST messages/recordings
Create new recording

* __Parameters__  
A json encoded associative array containing key and value pairs based on following fields
```json
{
    "name": "__String__",
    "description": "__Optional_String__",
}
```
* __Response__  
__recording_id__ of recently created recording record

* __Note__  
Media / recording will be uploaded separately using PUT messages/recordings/{recording_id}/media

### GET messages/recordings
list all exiting recordings, optionally client can filter recordings using query string (key value pair) in url, while using any of following fields
```
  name: __String__
  type: __String__, three digit file extension representing file type
  description: __String__
```

* __Response__  
an array of recordings indexed on recording_id

* __Note__  
Media / recording can be downloaded separately using GET messages/recordings/{recording_id}/media

### GET messages/recordings/{recording_id}
Read / view complete recording data

* __Parameters__  
Replace {recording_id} in url with valid recording_id

* __Response__  
recording details in associative array

### PUT messages/recordings/{recording_id}
Update an existing recording

* __Parameters__  
Replace {recording_id} in url with valid recording_id, fields require modifications will be POSTed in same way as `recordings`

* __Response__  
Return updated recording data as an associative array

### DELETE messages/recordings/{recording_id}
Delete an existing recording

* __Parameters__  
Replace {recording_id} in url with valid recording_id

### PUT messages/recordings/{recording_id}/media
Upload media / wave file for an existing recording, this method should be called followed by POST messages/recordings

* __URL Parameters__  
Replace {recording_id} in url with valid recording_id

* __POST Parameters__  
File contents and related headers

* __Response__  
recording_id of updated record

### GET messages/recordings/{recording_id}/media
Download wave file / media for an existing recording

* __Parameters__  
Replace {recording_id} in url with valid recording_id

* __Response__  
Download of Wave file / recording will be started

Email templates
---------------
### POST messages/templates
Create new template

* __Parameters__  
A json encoded associative array containing key and value pairs based on following fields
```json
{
    "name": "__String__",
    "description": "__Optional_String__",
    "subject": "__String__",
    "body": "__String__, HTML Message",
    "body_alt": "__Optional_String__, Plain Message",
    "type": "__Optional_String__, three digit file extension representing file type",
}
```
* __Response__  
__template_id__ of recently created template record

* __Note__  
Media / attachment will be uploaded separately using PUT messages/templates/{template_id}/media

### GET messages/templates
list all exiting templates, optionally client can filter templates using query string (key value pair) in url, while using any of following fields
```
  name: __String__
  type: __String__, three digit file extension representing file type
  description: __String__
  subject: __String__
  body: __String__
  body_alt: __String__
```

* __Response__  
an array of templates indexed on template_id

* __Note__  
Media / attachment can be downloaded separately using GET messages/templates/{template_id}/media

### GET messages/templates/{template_id}
Read / view complete template data

* __Parameters__  
Replace {template_id} in url with valid template_id

* __Response__  
template details in associative array

### PUT messages/templates/{template_id}
Update an existing template

* __Parameters__  
Replace {template_id} in url with valid template_id, fields require modifications will be POSTed in same way as `templates`

* __Response__  
Return updated template data as an associative array

### DELETE messages/templates/{template_id}
Delete an existing template

* __Parameters__  
Replace {template_id} in url with valid template_id

### PUT messages/templates/{template_id}/media
Upload media / attachment for an existing template, this method should be called followed by POST messages/templates

* __URL Parameters__  
Replace {template_id} in url with valid template_id

* __POST Parameters__  
File contents and related headers

* __Response__  
template_id of updated record

### GET messages/templates/{template_id}/media
Download attachment / media file for an existing template

* __Parameters__  
Replace {template_id} in url with valid template_id

* __Response__  
File / attachment download will be started

SMS Text Message
----------------
### POST messages/texts
Create new text

* __Parameters__  
A json encoded associative array containing key and value pairs based on following fields
```json
{
    "name": "__String__",
    "data": "__String__, Actual message",
    "type": "__Optional_String__, unicode or plain or binary",
    "description": "__Optional_String__",
}
```
* __Response__  
__text_id__ of recently created text record

### GET messages/texts
list all exiting texts, optionally client can filter texts using query string (key value pair) in url, while using any of following fields
```
  name: __String__
  data: __String__
  type: __String__, unicode or plain or binary
  description: __String__
```

* __Response__  
an array of texts indexed on text_id

### GET messages/texts/{text_id}
Read / view complete text data

* __Parameters__  
Replace {text_id} in url with valid text_id

* __Response__  
text details in associative array

### PUT messages/texts/{text_id}
Update an existing text

* __Parameters__  
Replace {text_id} in url with valid text_id, fields require modifications will be POSTed in same way as `texts`

* __Response__  
Return updated text data as an associative array

### DELETE messages/texts/{text_id}
Delete an existing text

* __Parameters__  
Replace {text_id} in url with valid text_id


Programs
========
General program related function
--------------------------------
### POST programs
To create program please use respective APIs separately designed for each type of program.

### GET programs
list all exiting programs, optionally client can filter programs using query string (key value pair) in url, while using any of following fields
```
  name: __String__
  type: __String__, program type
  parent_id: __Numeric_ID__, program_id of parent program
```

* __Response__  
an array of programs indexed on program_id

### GET programs/{program_id}
Read / view complete program data

* __Parameters__  
Replace {program_id} in url with valid program_id

* __Response__  
program details in associative array

### DELETE programs/{program_id}
Delete an existing program

* __Parameters__  
Replace {program_id} in url with valid program_id

### PUT programs/{program_name}/transmissions
Prepare a new transmission for program given its name, Actually its a shortcut way to create new transmissions. contrary to transmissions/{program_id} where user have to first create required program, this APIs complete both steps in one call.

* __Parameters__  
  * Replace {program_id} in url with valid program_id
  * A json encoded associative array containing key and value pairs based on following fields
```json
{
    "contact_id": "__Numeric_ID__, contact_id to contact where to transmit message",
    "account_id": "__Optional_Numeric_ID__, account_id of associated account",
    "direction": "__Optional_String__, either can be outbound or inbound",
    ".....": Further fields can be added, as per program requirement, for more details please see respective programs functions
}
```

* __Response__  
__transmission_id__ of recently created transmission record

### GET programs/{program_id}/transmissions
list all transmissions created using certain program which id is given in url, optionally client can filter transmissions using query string (key value pair) in url, while using any of following fields
```
  title: __String__
  contact_id: __Numeric_ID__, contact_id of target contact
  origin: __String__, reference to function / program which is responsible creation of this transmission
  status: __String__, if complete or failed
  response: __String__, the cause of error, transmission failure
```

* __Parameters__  
Replace {program_id} in url with any valid program_id or program type (as listed below), i.e `programs/emailtofax`

* __Response__  
an array of transmissions indexed on transmission_id

Email to Fax program
--------------------
### POST programs/emailtofax
Enable an account to receive emails from email address configured in account and forward them to fax

* __Parameters__  
A json encoded associative array containing key and value pairs based on following fields
```json
{
    "name": "__String__",
    "account_id": "__Numeric_ID__, account_id of account for which this program is being created"
}
```
* __Response__  
__program_id__ of recently created program record

Fax to Email program
-------------
### POST programs/faxtoemail
Enable an account to receive faxes and forward them to account's email address

* __Parameters__  
A json encoded associative array containing key and value pairs based on following fields
```json
{
    "name": "__String__",
    "account_id": "__Numeric_ID__, account_id of account for which this program is being created"
}
```
* __Response__  
__program_id__ of recently created program record

Receive Email program
---------------------
### POST programs/receiveemail
Enable an account to receive emails

* __Parameters__  
A json encoded associative array containing key and value pairs based on following fields
```json
{
    "name": "__String__",
    "account_id": "__Numeric_ID__, account_id of account for which this program is being created"
}
```
* __Response__  
__program_id__ of recently created program record

Receive FAX program
-------------------
### POST programs/receivefax
Enable an account to receive faxes

* __Parameters__  
A json encoded associative array containing key and value pairs based on following fields
```json
{
    "name": "__String__",
    "account_id": "__Numeric_ID__, account_id of account for which this program is being created"
}
```
* __Response__  
__program_id__ of recently created program record

Receive SMS program
-------------------
### POST programs/receivesms
Enable an account to receive SMS messages

* __Parameters__  
A json encoded associative array containing key and value pairs based on following fields
```json
{
    "name": "__String__",
    "account_id": "__Numeric_ID__, account_id of account for which this program is being created"
}
```
* __Response__  
__program_id__ of recently created program record

Send Email program
------------------
### POST programs/sendemail
Prepare given email message for provided account, and make it ready to be sent

* __Parameters__  
A json encoded associative array containing key and value pairs based on following fields
```json
{
    "name": "__String__",
    "template_id": "__Numeric_ID__, template_id of email template for which this program is being created",
}
```
* __Response__  
__program_id__ of recently created program record

Send FAX program
----------------
### POST programs/sendfax
Prepare given fax document for provided account, and make it ready to be sent

* __Parameters__  
A json encoded associative array containing key and value pairs based on following fields
```json
{
    "name": "__String__",
    "document_id": "__Numeric_ID__, document_id of fax document for which this program is being created",
}
```
* __Response__  
__program_id__ of recently created program record

Send SMS program
----------------
### POST programs/sendsms
Prepare given SMS for provided account, and make it ready to be sent

* __Parameters__  
A json encoded associative array containing key and value pairs based on following fields
```json
{
    "name": "__String__",
    "text_id": "__Numeric_ID__, text_id of SMS text for which this program is being created",
}
```
* __Response__  
__program_id__ of recently created program record

Voice Call with pre recorded message
------------------------------------
### POST programs/voicemessage
Prepare given voice recording for provided account, and make it ready to be played during call

* __Parameters__  
A json encoded associative array containing key and value pairs based on following fields
```json
{
    "name": "__String__",
    "recording_id": "__Numeric_ID__, recording_id of voice recording for which this program is being created",
}
```
* __Response__  
__program_id__ of recently created program record



Transmission - the actual call or action
========================================

create call request / dial / send message
-----------------------------------------
### POST transmissions
Create new transmission

* __Parameters__  
  * A json encoded associative array containing key and value pairs based on following fields
```json
{
    "title": "__String__",
    "program_id": "__Numeric_ID__, program_id of selected program",
    "account_id": "__Numeric_ID__, account_id of associated account",
    "contact_id": "__Numeric_ID__, contact_id of target contact",
    "origin": "__String__, reference to function / program which is responsible creation of this transmission",
    "direction": "__String__, either can be outbound or inbound",
}
```
* __Response__  
__transmission_id__ of recently created transmission record

### GET transmissions
list all exiting transmissions, optionally client can filter transmissions using query string (key value pair) in url, while using any of following fields
```
  title: __String__
  service_flag: __Numeric_ID__, Type of transmission service i.e Email::SERVICE_FLAG or Voice::SERVICE_FLAG
  account_id: __Numeric_ID__, account_id of associated account
  contact_id: __Numeric_ID__, contact_id of target contact
  program_id: __Numeric_ID__, program_id of program which will be used with this transmission
  origin: __String__, reference to function / program which is responsible creation of this transmission
  direction: __String__, either can be inbound or outbound
  status: __String__, if complete or failed
  response: __String__, the cause of error, transmission failure
```

* __Response__  
an array of transmissions indexed on transmission_id

### GET transmissions/{transmission_id}/send ( or /call or /dial )
Trigger already prepared transmission to dial / connect assigned contact and deliver desired message.  
__Note:__ call or dial synonymous can also be used in place of send

* __Parameters__  
Replace {transmission_id} in url with valid transmission_id

* __Response__  
transmission details in associative array

### PUT transmissions/{transmission_id}/schedule
Instead of delivering message instantly, schedule its delivery in near future.

* __Parameters__  
Replace {transmission_id} in url with valid transmission_id

* __Response__  
__schedule_id__ of recently created schedule record

### DELETE transmissions/{transmission_id}/schedule
Cancel any schedule associated with given transmission

* __Parameters__  
Replace {transmission_id} in url with valid transmission_id

### GET transmissions/{transmission_id}/retry
In case earlier transmission attempt failed, give it another try

* __Parameters__  
Replace {transmission_id} in url with valid transmission_id

### GET transmissions/{transmission_id}/clone
Want to resend an already completed transmission, copy it (Note: after copying, client still need to request send method for message delivery)

* __Parameters__  
Replace {transmission_id} in url with valid transmission_id

* __Response__  
__transmission_id__ of newly created transmission

### GET transmissions/{transmission_id}
Read / view complete transmission data

* __Parameters__  
Replace {transmission_id} in url with valid transmission_id

* __Response__  
transmission details in associative array


Reports
-------
### GET transmissions/{transmission_id}/status
Get current status of an existing transmission

* __Parameters__  
Replace {transmission_id} in url with valid transmission_id

* __Response__  
Will return one of the following status ( pending, processing, completed, failed, invalid )

### GET transmissions/{transmission_id}/detail
A list of attempts (spool) with their detail, which system has made to deliver that transmission

* __Parameters__  
Replace {transmission_id} in url with valid transmission_id

* __Response__  
Will return an array of spool record indexed on spool_id

### GET transmissions/{transmission_id}/result
Complete details of each step along with remote side responses, for requested transmission

* __Parameters__  
Replace {transmission_id} in url with valid transmission_id

* __Response__  
Will return a array of application results

### GET spools/{spool_id}/status
Get current status of an existing transmission attempt (spool)

* __Parameters__  
Replace {spool_id} in url with valid spool_id

* __Response__  
Will return one of the following status ( initiated, completed, failed )

### GET spools/{spool_id}/result
Complete details of each step along with remote side responses, for requested transmission attempt (spool_id)

* __Parameters__  
Replace {spool_id} in url with valid spool_id

* __Response__  
Will return a array of application results against given transmission attempt (spool_id)


User account / Email / DID / Extension
--------------------------------------
### POST accounts
Create new account

* __Parameters__  
A json encoded associative array containing key and value pairs based on following fields
```json
{
    "username": "__String__",
    "passwd": "__String__",
    "passwd_pin": "__String__",
    "first_name": "__Optional_String__",
    "last_name": "__Optional_String__",
    "phone": "__Digits__",
    "email": "__Email__",
    "address": "__Optional_String__",
    "active": "__Numeric__, 1 for active, 0 for disabled",
}
```
* __Response__  
__account_id__ of recently created account record

### GET accounts
list all exiting accounts, optionally client can filter accounts using query string (key value pair) in url, while using any of following fields
```
  username: __String__
  passwd: __String__
  passwd_pin: __String__
  first_name: __String__
  last_name: __String__
  phone: __Digits__
  email: __Email__
```

* __Response__  
an array of accounts indexed on account_id

### GET accounts/{account_id}
Read / view complete account data

* __Parameters__  
Replace {account_id} in url with valid account_id

* __Response__  
account details in associative array

### PUT accounts/{account_id}
Update an existing account

* __Parameters__  
Replace {account_id} in url with valid account_id, fields require modifications will be POSTed in same way as `accounts`

* __Response__  
Return updated account data as an associative array

### DELETE accounts/{account_id}
Delete an existing account

* __Parameters__  
Replace {account_id} in url with valid account_id

### PUT accounts/{account_id}/programs/{program\_id}
Subscribe an account to some exiting program, i.e enable an account to use a certain program for inbound transmissions

* __Parameters__  
  * Replace {account_id} in url with valid account_id
  * Replace {program_id} in url with some existing program's program_id

### DELETE accounts/{account_id}/programs/{program\_id}
Unsubscribe an account from given program, i.e stop an account from using certain program

* __Parameters__  
  * Replace {account_id} in url with valid account_id
  * Replace {program_id} in url with valid program name or program_id

### DELETE accounts/{account_id}/programs
Clear an account from all subscribed programs

* __Parameters__  
  * Replace {account_id} in url with valid account_id

### PUT accounts/{account_id}/users/{user\_id}
Change account ownership, assign account to some other user

* __Parameters__  
  * Replace {account_id} in url with valid account_id
  * Replace {user_id} in url with valid user_id

### DELETE accounts/{account_id}/users
Dissociate a account from any user, make it free to assign

* __Parameters__  
  * Replace {account_id} in url with valid account_id


User Management
---------------
### POST users
Create new user

* __Parameters__  
A json encoded associative array containing key and value pairs based on following fields
```json
{
    "username": "__String__",
    "password": "__String__",
    "first_name": "__Optional_String__",
    "last_name": "__Optional_String__",
    "phone": "__Digits__",
    "email": "__Email__",
    "address": "__Optional_String__",
    "company": "__Optional_String__",
    "country_id": "__Optional_Numeric_ID__, see country table",
    "timezone_id": "__Numeric_ID__, see timezone table",
    "active": "__Numeric__, 1 for active, 0 for disabled",
}
```
* __Response__  
__user_id__ of recently created user record

### GET users
list all exiting users, optionally client can filter users using query string (key value pair) in url, while using any of following fields
```
  username: __String__
  first_name: __String__
  last_name: __String__
  phone: __Digits__
  email: __Email__
```

* __Response__  
an array of users indexed on user_id

### GET users/{user_id}
Read / view complete user data

* __Parameters__  
Replace {user_id} in url with valid user_id

* __Response__  
user details in associative array

### PUT users/{user_id}
Update an existing user

* __Parameters__  
Replace {user_id} in url with valid user_id, fields require modifications will be POSTed in same way as `users`

* __Response__  
Return updated user data as an associative array

### DELETE users/{user_id}
Delete an existing user

* __Parameters__  
Replace {user_id} in url with valid user_id

### PUT users/{user_id}/roles/{role\_id}
Assign a role ( a set of permissions ) to user

* __Parameters__  
  * Replace {account_id} in url with valid account_id
  * Replace {role_id} in url with valid role_id
  
### DELETE users/{user_id}/roles/{role\_id}
Drop a role ( a set of permissions ) from user

* __Parameters__  
  * Replace {account_id} in url with valid account_id
  * Replace {role_id} in url with valid role_id

### PUT users/{user_id}/permissions/{permission\_id}
Assign certain permission to user (If client does't want to assign complete role)

* __Parameters__  
  * Replace {account_id} in url with valid account_id
  * Replace {permission_id} in url with valid permission_id
  
### DELETE users/{user_id}/permissions/{permission\_id}
Drop an already assigned permission from user (Please note it will drop permissions assigned by role)

* __Parameters__  
  * Replace {account_id} in url with valid account_id
  * Replace {permission_id} in url with valid permission_id

### POST users/authenticate
Authenticate a user by sending credentials via post request

A json encoded associative array containing key and value pairs based on following fields
```json
{
    "username": "__String__",
    "password": "__String__"
}
```

Trunk / Termination Providers APIs
----------------------------------
### POST providers
Create new provider

* __Parameters__  
A json encoded associative array containing key and value pairs based on following fields
```json
{
    "name": "__String__",
    "gateway_flag": "__Numeric_ID__, Type of gateway i.e Freeswitch::GATEWAY_FLAG or Kannel::GATEWAY_FLAG",
    "service_flag": "__Numeric_ID__, Type of transmission service i.e Email::SERVICE_FLAG or Voice::SERVICE_FLAG",
    "node_id": "__Optional_Numeric_ID__, see node table",
    "host": "__IPAddress__, ip address to termination server",
    "port": "__Optional_Digits__",
    "username": "__Optional_String__",
    "password": "__Optional_String__",
    "dialstring": "__Optional_String__",
    "prefix": "__Optional_Digits__, number which is required to be dialed before actual phone number",
    "settings": "__Optional_String__, any additional configuration required by this provider",
    "register": "__Optional_Boolean__ 1 for yes, 0 for no",
    "weight": "__Optional_Numeric__, provider having lighter weight will be used more frequently",
    "type": "__Optional_String__",
    "active": "__Numeric__, 1 for active, 0 for disabled",
}
```
* __Response__  
__provider_id__ of recently created provider record

### GET providers
list all exiting providers, optionally client can filter providers using query string (key value pair) in url, while using any of following fields
```
  name: __String__
  gateway_flag: __Numeric_ID__, Type of gateway i.e Freeswitch::GATEWAY_FLAG or Kannel::GATEWAY_FLAG
  service_flag: __Numeric_ID__, Type of transmission service i.e Email::SERVICE_FLAG or Voice::SERVICE_FLAG
  host: __IPAddress__, ip address to termination server
  active: __Numeric__, 1 for active, 0 for disabled
```

* __Response__  
an array of providers indexed on provider_id

### GET providers/{provider_id}
Read / view complete provider data

* __Parameters__  
Replace {user_id} in url with valid provider_id

* __Response__  
provider details in associative array

### PUT providers/{provider_id}
Update an existing provider

* __Parameters__  
Replace {user_id} in url with valid provider_id, fields require modifications will be POSTed in same way as `providers`

* __Response__  
Return updated provider data as an associative array

### DELETE providers/{provider_id}
Delete an existing provider

* __Parameters__  
Replace {user_id} in url with valid provider_id


