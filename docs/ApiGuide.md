FORMAT: 1A
HOST: http://ictcore.example.com

# ICTCore REST APIs Guide

## Overview

* __API Endpoint__ : Domain / web url corresponding address for ictcore/wwwroot installation directory.
* __API Access__ : Any valid combination of username and password created in usr table.
* __POST Method__: Any data submitted to POST method based APIs must be encoded as json.
* __DELETE Method__: user can use both DELETE and GET methods for delete APIs.
* __List APIs__: All list APIs support optional search filter, to search user need to use search parameters in url as query string using key value pair.

### HTTP Response Code

* __200__ Function successfully executed.
* __401__ Invalid or missing username or password.
* __403__ Permission denied. User is not permitted to perform requested action.
* __404__ Invalid API location or request to none existing resource. Check the URL and posting method (GET/POST).
* __412__ Data validation failed.
* __417__ Unexpected error.
* __423__ System is not ready to perform requested action.
* __500__ Internal server error. Try again at a later time.
* __501__ Feature not implemented.

# Group Authenticate

## Authentication [/authenticate]

Create and return authentication token / session key.

### Authenticate parameter [POST]

__Note:__ Unlike other APIs this API does not require separate authentication in header

+ Request (application/json)

    + Attributes

        + username: admin (string) - api username for authentication
        + password: mysecret (string) - api password for authentication

+ Response 200 (application/json)
    
    + Attributes 
        
        + token : token (string) 

# Group Program

## General program related function [/programs]

To create program please use respective APIs separately designed for each type of program.

### Get all Programs [GET]

+ Request

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

    + Attributes (array[Program])

## Single Program [/programs/{program_id}]

+ Parameters

    + program_id (number) - ID of the program in the form of an integer

### View a Program Detail [GET]

+ Request

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

    + Attributes (Program)

### Delete Program [DELETE]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

+ Response 200

## Shortcut way to create new Transmissions [/programs/{program_id}/transmissions]

+ Parameters

    + program_id (number) - ID of the program in the form of an Intiger


### New Transmission [PUT]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

    + Attributes (Transmission)

+ Response 200 (application/json)

    + Attributes

        + transmission_id: 1 (number) - transmission id of recently created transmission

### Get Transmission [GET]

+ Request

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

    + Attributes (Transmission)


# Group Email to Fax program

##  Email to Fax program [/programs/emailtofax]

### Create New Email to Fax program [POST]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

    + Attributes (ProgramEmailtofax)

+ Response 200 (application/json)

    + Attributes
        
        + program_id : 1 (number) - program id  of recently created program
            

# Group Fax to Email program

##  Fax to Email program [/programs/faxtoemail]

### Create New  Fax to Email program [POST]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

    + Attributes (ProgramFaxtoemail)

+ Response 200 (application/json)

    + Attributes

        + program_id : 1 (number) - program id  of recently created program

# Group Receive Email program

##  Receive Email program [/programs/receiveemail]

### Create New  Receive Email program [POST]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

    + Attributes (ProgramReceiveemail)

+ Response 200 (application/json)

    + Attributes

        + program_id : 1 (number) - program id  of recently created program


# Group Receive FAX program

##  Receive FAX program [/programs/receivefax]

### Create New  Receive FAX program [POST]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

    + Attributes (ProgramReceivefax)

+ Response 200 (application/json)

    + Attributes

        + program_id : 1 (number) - program id  of recently created program


# Group Receive SMS program

##  Receive SMS program [/programs/receivesms]

### Create New  Receive SMS program [POST]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

    + Attributes (ProgramReceivesms)

+ Response 200 (application/json)

    + Attributes

        + program_id : 1 (number) - program id  of recently created program


# Group Send Email program

## Send Email program [/programs/sendemail]

### Create New  Send Email program [POST]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

    + Attributes (ProgramSendemail)

+ Response 200 (application/json)

    + Attributes

        + program_id : 1 (number) - program id  of recently created program

# Group Send FAX program

## Send FAX program [/programs/sendfax]

Prepare given fax document for provided account, and make it ready to be sent

### Create New  Send FAX program [POST]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

    + Attributes (ProgramSendfax)

+ Response 200 (application/json)

    + Attributes

        + program_id : 1 (number) - program id  of recently created program

# Group Send SMS program

## Send SMS program [/programs/sendsms]

Prepare given SMS for provided account, and make it ready to be sent

### Create New  Send SMS program [POST]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

    + Attributes (ProgramSendsms)

+ Response 200 (application/json)

    + Attributes

        + program_id : 1 (number) - program id  of recently created program

# Group Voice Call with pre recorded message

## Voice Message program [/programs/voicemessage]

Prepare given voice recording for provided account, and make it ready to be played during call

### Create New  Voice Message program [POST]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

    + Attributes (ProgramVoicemessage)

+ Response 200 (application/json)

    + Attributes
              
        + program_id : 1 (number) - program id  of recently created program

## Voice TTS program [/programs/voicetts]

Prepare given voice tts for provided account, and make it ready to be played during call

### Create New Voice TTS program [POST]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

    + Attributes (ProgramVoicetts)

+ Response 200 (application/json)

    + Attributes

        + program_id : 1 (number) - program id  of recently created program        
            

# Group Transmission - the actual call or action

create call request / dial / send message

## Collection of Transmission [/transmissions]

### Create New Transmission [POST]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

    + Attributes (Transmission)

+ Response 200 (application/json)

    + Attributes
            
        + text_id: 1 (number) - text id of recently created template

### Get all Transmissions [GET]

+ Request

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

    + Attributes (Transmission)

## single Transmission  [/transmissions/{transmission_id}]

+ Parameters

    + transmission_id (number) - ID of the transmission in the form of an integer

### Get a Transmission [GET]

+ Request

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

    + Attributes (Transmission)

### Delete Transmission [DELETE]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

+ Response 200

## Transmition Send [/transmissions/{transmission_id}/send]

+ Parameters

    + transmission_id (number) - ID of the transmission in the form of an integer

### Send Transmition [POST]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

    + Attributes
        
        + spool_id: 1 (number) - Spool ID of resulted attempt
            

## Transmition Retry [/transmissions/{transmission_id}/retry]

+ Parameters

    + transmission_id (number) - ID of the transmission in the form of an integer

### Retry Transmition [POST]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

    + Attributes
        
        + spool_id: 1 (number) - Spool ID of resulted attempt
            

## Transmission Schdule [/transmissions/{transmission_id}/schedule]

+ Parameters

    + transmission_id (number) - ID of the transmission in the form of an integer

### Schdule Transmission [PUT]

Instead of delivering message instantly, schedule its delivery in near future.

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

    + Attributes
        
        + schedule_id: 1 (number) - schedule id of recently created schedule record


### Delete Transmission schedule [DELETE]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

+ Response 200

## Transmission Clone [/transmissions/{transmission_id}/clone]

+ Parameters

    + transmission_id (number) - ID of the transmission in the form of an integer

### Create Transmission Clone [GET]

Want to resend an already completed transmission, copy it (Note: after copying, client still need to request send method for message delivery)

+ Request

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

    + Attributes
        
        + transmission_id: 1 (number) - transmission id of newly created transmission


# Group Contact

## Collection of Contacts [/contacts]

### Create New Contact [POST]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

    + Attributes (Contact)

+ Response 200 (application/json)

    + Attributes

        + contact_id : 1 (number) - id of recently created contact
            

### Get All Contacts [GET]

+ Request

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

  + Attributes (array[Contact])

## Single Contact [/contacts/{contact_id}]

+ Parameters

    + contact_id (number) - ID of the contact in the form of an integer


### View a Contact Detail [GET]

+ Request

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

    + Attributes (Contact)

### Update Contacts [PUT]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

    + Attributes (Contact)

+ Response 200 (application/json)

    + Attributes (Contact)

### Delete Contact [DELETE]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

## Contact association with group [/contacts/{contact_id}/link/{group_id}]

+ Parameters

    + contact_id (number) - ID of the contact in the form of an integer
    + group_id (number) - ID of the group in the form of an integer

### Create association [PUT]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

     + Attributes (Contact)

### Delete association [DELETE]

remove selected contact from provided contact group

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

     + Attributes (Contact)


# Group Contact Group

## Collection of Groups [/groups]

### Create New Group [POST]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

    + Attributes (Group)

+ Response 200 (application/json)

    + Attributes

        + group_id : 1 (number) - return newly created id 
            

### Get All Groups [GET]

+ Request

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

  + Attributes (array[Group])

## Single Group [/groups/{group_id}]

+ Parameters

    + group_id (number) - ID of the group in the form of an integer

### View a Group Detail [GET]

+ Request

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

    + Attributes (Group)

### Update Groups [PUT]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

    + Attributes (Group)

+ Response 200 (application/json)

    + Attributes (Group)

### Delete Group [DELETE]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

+ Response 200

## Group's Contacts collection [/groups/{group_id}/contacts]

+ Parameters

    + group_id (number) - ID of the group in the form of an integer

### Get all Contacts from Group [GET]

+ Request

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

    + Attributes (array[Contact])

## CSV Contact Import / Export for Group [/groups/{group_id}/csv]

+ Parameters

    + group_id (number) - ID of the group in the form of an integer

### Import Contacts into Group [POST]

+ Request (text/csv)

    + Headers

            Authentication: Bearer JWT

    + Body

            "CSV file contents"

+ Response 200

### Export Contacts from Group [GET]

Download complete contacts from selected contact group as csv file

+ Request

    + Headers

            Authentication: Bearer JWT

+ Response 200 (text/csv)

    + Body

            "CSV file contents"

## Sample Csv File [/groups/sample/csv]

Download a sample csv file

### Get Sample csv file [GET]

+ Request

    + Headers

            Authentication: Bearer JWT

+ Response 200 (text/csv)

    + Body

            "CSV Sample file contents"


# Group Campaign - the actual bulk system

Create campaign for message delivery / calling bulk contacts

## Campaign collection [/campaigns]

### Create New Campaign [POST]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

    + Attributes (Campaign)

+ Response 200 (application/json)

    + Attributes
        
        + campaign_id: 1 (number) - campaign id of recently created campaign record


### Get all Campaign [GET]

+ Request

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

    + Attributes (array[Campaign])

## Single Campaign Action [/campaigns/{campaign_id}]

+ Parameters

    + campaign_id (number) - ID of the campaign in the form of an integer

### Update Campaign [PUT]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

    + Attributes (Campaign)

+ Response 200 (application/json)

    + Attributes (Campaign)

### Get a Campaign [GET]

+ Request

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

    + Attributes (Campaign)

### Delete Campaign [DELETE]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

+ Response 200

## Campaign Start [/campaigns/{campaign_id}/start]

+ Parameters

    + campaign_id (number) - ID of the campaign in the form of an integer

### Campaign Start [PUT]

Start contact processing / calling in selected campaign

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

    + Attributes
        
        + Boolean: true on success (string) - Boolean: true on success


## Campaign Stop [/campaigns/{campaign_id}/stop]

+ Parameters

    + campaign_id (number) - ID of the campaign in the form of an integer

### Campaign Stop [PUT]

Stop contact processing / calling in selected campaign

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

    + Attributes
        
        + Boolean: true on success (string) - Boolean: true on success

## Campaign schedule [/campaigns/{campaign_id}/start/schedule]

+ Parameters

    + campaign_id (number) - ID of the campaign in the form of an integer


### Start Campaign Schedule [POST]

Instead of processing campaign contacts instantly, schedule their processing / calling in near future.

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

    + Attributes
               
        + schedule_id: 1 (number) - schedule id of recently created schedule record
            

## Campaign schedule [/campaigns/{campaign_id}/stop/schedule]

+ Parameters

    + campaign_id (number) - ID of the campaign in the form of an integer


### Stop Campaign Schedule [POST]

Instead manually stopping a campaign, we can schedule it.

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

    + Attributes
        
        + schedule_id: 1 (number) - schedule id of recently created schedule record

## Delete Campaign schedule [/campaigns/{campaign_id}/schedule]

+ Parameters

    + campaign_id (number) - ID of the campaign in the form of an integer


### Stop Campaign Schedule [POST]

Cancel any schedule associated with given campaign

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

+ Response 200

# Group Message

There are different kinds of messages like fax,voice,sms and email

## Fax Documents [/messages/documents]

### Create New Document [POST]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

    + Attributes (DocumentPost)

+ Response 200 (application/json)

    + Attributes

        + document_id: 1 (number) - document id of recently created document record


### Get all Documnets [GET]

+ Request

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

    + Attributes (array[Document])

## Single Document [/messages/documents/{document_id}]

Note: Media / document can be downloaded separately using GET messages/documents/{document_id}/media

+ Parameters

    + document_id (number) - ID of the document in the form of an integer

### View a Document Detail [GET]

+ Request

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

    + Attributes (Document)

### Update Document [PUT]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

    + Attributes (Document)

+ Response 200 (application/json)

    + Attributes (Document)

### Delete Document [DELETE]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

+ Response 200

## Add Document File [/messages/documents/{document_id}/media]

+ Parameters

    + document_id (number) - ID of the document in the form of an integer

### Add / Update Document file [PUT]

Upload media / pdf file for an existing document, this method should be called followed by POST messages/documents

+ Request (application/pdf)

    + Headers

            Authentication: Bearer JWT

    + Body

            "Pdf file contents"

+ Response 200 (application/json)

    + Attributes

        + document_id: 1 (number) - document id of updated record

### Get Document [GET]

Download Document file

+ Request

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/pdf)

    + Body

            "Pdf file contents"

# Group Voice Recordings

## Collection of Voice Recordings [/messages/recordings]

### Create New Recording [POST]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

    + Attributes (Document)

+ Response 200 (application/json)

    + Attributes

        + recording_id: 1 (number) - recording id of recently created recording
                

### Get all Recording [GET]

Note: Media / recording can be downloaded separately using GET messages/recording/{recording_id}/media

+ Request

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

    + Attributes (array[Document])

## Single Recording [/messages/recordings/{recording_id}]

+ Parameters

    + recording_id (number) - ID of the recording in the form of an integer

### View a Recording Detail [GET]

+ Request

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

    + Attributes (Document)

### Update Recording [PUT]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

    + Attributes (Document)

+ Response 200 (application/json)

    + Attributes (Document)


### Delete Recording [DELETE]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

+ Response 200

## Add Recording File [/messages/recordings/{recording_id}/media]

+ Parameters

    + recording_id (number) - ID of the recording in the form of an integer

### Add / Update Recoridng File [PUT]

Upload media / wav file for an existing recording, this method should be called followed by POST messages/recording

+ Request (audio/wav)

    + Headers

            Authentication: Bearer JWT

    + Body

            "Recording file contents"

+ Response 200 (application/json)

    + Attributes

        + recording_id: 1 (number) - recording id of updated record

### Get Recording [GET]

Download wav file

+ Request

    + Headers

            Authentication: Bearer JWT

+ Response 200 (audio/wav)

    + Body

            "Recording file contents"

# Group Email templates

## Collection of Email Templates [/messages/templates]

### Create New Template [POST]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

    + Attributes (Template)

+ Response 200 (application/json)

    + Attributes

        + template_id: 1 (number) - template id of recently created template
            

### Get all Templates [GET]

Note: Media / attachment can be downloaded separately using GET messages/templates/{template_id}/media

+ Request

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

    + Attributes (array[Template])

## Single Template [/messages/templates/{template_id}]

+ Parameters

    + template_id (number) - ID of the template in the form of an integer

### View a Template Detail [GET]

+ Request

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

    + Attributes (Template)

### Update Template [PUT]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

    + Attributes (Template)

+ Response 200 (application/json)

    + Attributes (Template)

### Delete Template [DELETE]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

+ Response 200

## Add Template File [/messages/templates/{template_id}/media]

+ Parameters

    + template_id (number) - ID of the template in the form of an integer

### Add / Update Template File [PUT]

Upload media / attachment for an existing template, this method should be called followed by POST messages/templates

+ Request (text/plain)

    + Headers

            Authentication: Bearer JWT

    + Body

            "Email template contents"

+ Response 200 (application/json)

    + Attributes

        + template_id: 1 (number) - template id of updated record

### Get Template file [GET]

Download file

+ Request

    + Headers

            Authentication: Bearer JWT

+ Response 200 (text/html)

    + Body

            "Email template contents"

# Group SMS Text Message

## Collection of SMS Text Messages [/messages/texts]

### Create New Texts [POST]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

    + Attributes (Text)

+ Response 200 (application/json)

    + Attributes
    
        + text_id: 1 (number) - text id of recently created template


### Get all Text Messages [GET]

+ Request

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

    + Attributes (array[Text])

## Single Text [/messages/texts/{text_id}]

+ Parameters

    + text_id (number) - ID of the text in the form of an integer

### View a Text Detail [GET]

+ Request

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

    + Attributes (Text)

### Update Text [PUT]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

    + Attributes (Text)

+ Response 200 (application/json)

    + Attributes (Text)


### Delete Text [DELETE]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

+ Response 200


# Group Reports

## Get Transmission Status Report [/transmissions/{transmission_id}/status]

+ Parameters

    + transmission_id (number) - ID of the transmission in the form of an integer

### Transmission Status [GET]

Get current status of an existing transmission

+ Request

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

    + Attributes
                
        + Status: status (string) - Will return one of the following status (pending, processing, completed, failed, invalid)


## Get Transmission detail Report [/transmissions/{transmission_id}/detail]

+ Parameters

    + transmission_id (number) - ID of the transmission in the form of an integer

### Transmission Detail [GET]

A list of attempts (spool) with their detail, which system has made to deliver that transmission

+ Request

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

    + Attributes (array[Spool])

## Get Transmission result Report [/transmissions/{transmission_id}/result]

+ Parameters

    + transmission_id (number) - ID of the transmission in the form of an integer

### Transmission Result [GET]

Complete details of each step along with remote side responses, for requested transmission

+ Request

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

    + Attributes (array[Result])

## Get Spool result status [/spools/{spool_id}/status]

+ Parameters

    + spool_id (number) - ID of the spool in the form of an integer

### Spool Status [GET]

Get current status of an existing transmission attempt (spool)

+ Request

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

    + Attributes
        
        + status: status (string) - Will return one of the following status (initiated, completed, failed)


## Get Spool result result [/spools/{spool_id}/result]

+ Parameters

    + spool_id (number) - ID of the spool in the form of an integer

### Spool Result [GET]

Complete details of each step along with remote side responses, for requested transmission attempt `spool_id`

+ Request

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

    + Attributes (array[Result])

# Group User account / Email / DID / Extension

## Users Acounts [/accounts]

### Create Account [POST]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

    + Attributes (Account)

+ Response 200 (application/json)

    + Attributes
         
         + account_id: 1 (number) - account id of recently created account record

### Get All Accounts [GET]

+ Request

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

    + Attributes (array[Account])


## Single Account Detail [/accounts/{account_id}]

+ Parameters

    + account_id (number) - ID of the account in the form of an integer

### View a Account [GET]

Read / view complete account data

+ Request

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

    + Attributes (Account)

### Update Account [PUT]

Update an existing account

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

    + Attributes (Account)

+ Response 200 (application/json)

   + Attributes (Account)

### Delete Account [DELETE]

Delete an existing account

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

+ Response 200

## Subscribe an account to some exiting program [/accounts/{account_id}/programs/{program_id}]

+ Parameters

    + account_id (number) - ID of the account in the form of an integer
    + program_id (number) - ID of the program in the form of an integer

### Subscribe an account to some exiting program [PUT]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

    + Attributes (Account)

### Unsubscribe an account from given program [DELETE]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

    + Attributes (Account)

## Clear an account from all subscribed programs [/accounts/{account_id}/programs]

+ Parameters

    + account_id (number) - ID of the account in the form of an integer

### Clear an account all subscribed program [DELETE]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

    + Attributes (Account)

## Change account ownership [/accounts/{account_id}/users/{user_id}]

+ Parameters

    + account_id (number) - ID of the account in the form of an integer
    + user_id (number) - ID of the user in the form of an integer

###  Change account ownership assign account to some other user [PUT]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

    + Attributes (Account)

## Dissociate a account from any user [/accounts/{account_id}/users]

+ Parameters

    + account_id (number) - ID of the account in the form of an integer

### Dissociate a account from any user, make it free to assign [DELETE]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

    + Attributes (Account)


## Account Settings [/accounts/{account_id}/settings/{name}]

+ Parameters

    + account_id (number) - ID of the account in the form of an integer
    + name (string) - Account setting name

### View a Account Setting [GET]

Read / view account setting

+ Request

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

    + Attributes

        + value: value (string) - will return current value of the given account setting

### Update Account Setting [PUT]

Create a new or update an existing account setting

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

    + Attributes (Account)

+ Response 200

### Delete Account Setting [DELETE]

Delete an existing account setting

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

+ Response 200

# Group User Management

## User Collection [/users]

### Create New User [POST]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

    + Attributes (User)

+ Response 200 (application/json)

    + Attributes
        
        + user_id: 1 (number) - user id of recently created user record

### Get All Users [GET]

list all exiting users, optionally client can filter users using query string (key value pair) in url, while using any of following fields

+ Request

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

    + Attributes (array[User])

## Single User Detail [/users/{user_id}]

+ Parameters

    + user_id (number) - ID of the user in the form of an integer

### View a User Detail [GET]

+ Request

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

    + Attributes (User)

### Update User [PUT]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

    + Attributes (User)

+ Response 200 (application/json)

    + Attributes (User)

### Delete User [DELETE]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

+ Response 200

## User Password [/users/{user_id}/password]

+ Parameters

    + user_id (number) - ID of the user in the form of an integer

### Update Password [PUT]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

    + Attributes

        + password : mysecret (string, required) - New user password 

+ Response 200 (application/json)

    + Attributes (User)

### Export All Users via CSV [/users/csv]

list all exiting users, optionally client can filter users using query string (key value pair) in url, while using any of following fields

+ Request

    + Headers

            Authentication: Bearer JWT

+ Response 200 (text/csv)

    + csv (array[User])

### Import All Users via CSV [/users/csv]

list all exiting users, optionally client can filter users using query string (key value pair) in url, while using any of following fields

+ Request

    + Headers

            Authentication: Bearer JWT
    + Body

            "CSV file contents"

+ Attributes (user)

## User Role Define [/users/{user_id}/roles/{role_id}]

+ Parameters

    + user_id (number) - ID of the user in the form of an Integer
    + role_id (number) - ID of the role in the form of an Integer

### Update User Role [PUT]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

    + Attributes (User)

### Delete User Role [DELETE]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

+ Response 200

## User permissions Define [/users/{user_id}/permissions/{permission_id}]

+ Parameters

    + user_id (number) - ID of the user in the form of an Integer
    + permission_id (number) - ID of the permission in the form of an Integer

### Update User Permissions [PUT]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

    + Attributes (User)

### Delete User Permissions [DELETE]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

+ Response 200

# Group Trunk / Termination Providers APIs

## Trunk Termination [/providers]

### Create Provider [POST]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

    + Attributes (Provider)

+ Response 200 (application/json)

    + Attributes
         
         + provider_id: 1 (number) -  provider id of recently created provider record


### Get All Providers [GET]

+ Request

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

    + Attributes (array[Provider])

## Single Provider Action [/providers/{provider_id}]

+ Parameters

    + provider_id (number) - ID of the user in the form of an integer

### Update Provider [PUT]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

    + Attributes (Provider)

+ Response 200 (application/json)

     + Attributes (Provider)

### View a Provider Detail [GET]

+ Request

    + Headers

            Authentication: Bearer JWT

+ Response 200 (application/json)

    + Attributes (Provider)

### Delete Providers [DELETE]

+ Request (application/json)

    + Headers

            Authentication: Bearer JWT

+ Response 200


# Data Structures

## Statistics (object)
+ campaign_total: 1 (number) - total number of campaign
+ campaign_active: 0 (number) -total number of active campaign
+ group_total: 12 (number) - number of groups
+ contact_total: 5 (number) - number of contacts
+ transmission_total: 1 (number) - number of transmistions
+ transmission_active: 1 (number) - number of active transmission
+ spool_total: 1 (number) - number of spools
+ spool_success: 1 (number) - number of success spools
+ spool_failed: 1 (number) - number of unsuccessed spools

## Contact (object)
+ first_name: first name (string, required)
+ last_name: last name (string, optional)
+ phone:   03001234567 (number, required)
+ email:  email (string, optional)
+ address: address (string, optional)
+ custom1: custom1 (string, optional)
+ custom2: custom 2 (string, optional)
+ custom3: custom 3 (string, optional)
+ description: description (string, optional)

## Group (object)
+ id: 1 (number, default) - id is auto increment
+ name: group name (string,required)
+ description: Description (string, optional)

## DocumentPost (object)
+ name: group name (string,required)
+ description: Description (string, optional)

## Document (DocumentPost)
+ type: type (string) - three digit file extension representing file type

## Template (object)
+ name: name (string, required)
+ description: Description (string, optional)
+ subject: Subject (string, required)
+ body:  body (string, required) - HTML Message
+ body_alt: body alt (string, optional) - Plain Message
+ type: upload file (string, optional) - three digit file extension representing file type

## Text (object)
+ name: name (string, required)
+ data: Data (string, required) - Actual message
+ type: type (string, optional) - unicode or plain or binary
+ description: Description (string, optional)

## Program (object)
+ name: name (string, required)
+ type: type (string, optional) - program type
+ parent_id: 1 (number, optional) - program id of parent program

## TransmissionPost (object)
+ title: title (string)
+ origin:  origin (string) -  reference to function / program which is responsible creation of this transmission
+ contact_id:  1 (number, required) - contact id to contact where to transmit message
+ account_id:  1 (number) -  account id of associated account
+ service_flag:  1 (number) - Type of transmission service i.e Email::SERVICE_FLAG or Voice::SERVICE_FLAG
+ program_id:  1 (number, required) - program id of program which will be used with this transmission
+ direction:  direction (string) - either can be outbound or inbound

## Transmission (TransmissionPost)
+ status:  status (string) - if complete or failed
+ response: response (string) - the cause of error, transmission failure

## ProgramEmailtofax (Program)
+ account_id: 1 (number) - account id of account for which this program is being created

## ProgramFaxtoemail (Program)
+ account_id: 1 (number) - account id of account for which this program is being created

## ProgramReceivefax (Program)
+ account_id: 1 (number) - account id of account for which this program is being created

## ProgramSendfax (Program)
+ document_id: 1 (number) - document id of fax document for which this program is being created

## ProgramReceiveemail (Program)
+ account_id: 1 (number) - account id of account for which this program is being created

## ProgramSendemail (Program)
+ template_id: 1 (number) - template id of email template for which this program is being created

## ProgramReceivesms (Program)
+ account_id: 1 (number) - account id of account for which this program is being created

## ProgramSendsms (Program)
+ text_id: 1 (number) - text id of SMS text for which this program is being created

## ProgramVoicemessage (Program)
+ recording_id: 1 (number) - recording id of voice recording for which this program is being created

## Account (object)
+ username: username (string)
+ passwd: password (string)
+ passwd_pin: password pin (string)
+ first_name: first name (string)
+ last_name: last name (string)
+ phone: 03001234567 (number)
+ email: email (string)
+ address: address (string)
+ active: 1 (number) - 1 for active, 0 for disabled

## User (object)
+ username: username (string)
+ passwd: password (string)
+ first_name: first name (string)
+ last_name: last name (string)
+ phone: 03001234567 (number)
+ email: email (string)
+ address: address (string)
+ company: company name (string)
+ country_id: 1 (number) - see country table
+ timezone_id: 1 (number) - see timezone table
+ active: 1 (number) - 1 for active, 0 for disabled

## Provider (object)
+ name: name (string)
+ gateway_flag: 1 (number) - Type of gateway i.e Freeswitch::GATEWAY_FLAG or Kannel::GATEWAY_FLAG
+ service_flag: 1 (number) - Type of transmission service i.e Email::SERVICE_FLAG or Voice::SERVICE_FLAG
+ node_id: 1 (number, optional) - see node table
+ host: ipaddress (string) - ip address to termination server
+ port: 8080 (number, optional)
+ username: username (string,required)
+ password: password (string, optional)
+ dialstring: dailstring (string, optional)
+ prefix: 12 (number, optional) -number which is required to be dialed before actual phone number
+ settings: settings (string, optional) - any additional configuration required by this provider
+ register: 1 (number, optional) 1 for yes, 0 for no
+ weight: 10 (number) provider having lighter weight will be used more frequently
+ type: type (string)
+ active: 1 (number) 1 for active, 0 for disabled

## Campaign (object)
+ program_id: 1 (number)
+ group_id: 2 (number)
+ cpm: 2 (number) - transmissions / cycles per second
+ try_allowed: 2 (number)
+ account_id: 1 (number) - account_id of associated account
+ status: active (string) - current status of campaign

## Spool (object)
+ spool_id: 1 (number)
+ time_spool: 1518705479 (number)
+ time_start: 1518705479 (number)
+ time_connect: 1518705479 (number)
+ time_end: 1518705479 (number)
+ status: completed (string)
+ response: busy (string)
+ transmission_id: 1 (number)

## Result (object)
+ spool_result_id: 1 (number)
+ spool_id: 1 (number)
+ type: dtmf (string) - type of result
+ name: age (string) - result id / name
+ data: 22 (string) - actual result

