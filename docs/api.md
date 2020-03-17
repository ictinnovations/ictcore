ICTCore REST APIs Guide
=======================

Authentication
--------------
* POST authenticate
* POST authenticate/cancel

System Statistics
-----------------
* GET statistics

Contact / pre defined destination number
----------------------------------------
* POST contacts
* GET contacts
* GET contacts/{contact_id}
* PUT contacts/{contact_id}
* DELETE contacts/{contact_id}
* PUT contacts/{contact_id}/link/{group_id}
* DELETE contacts/{contact_id}/link/{group_id}

### Contact Group
* POST groups
* GET groups
* GET groups/{group_id}
* GET groups/{group_id}/contacts
* PUT groups/{group_id}
* DELETE groups/{group_id}
* POST groups/{group_id}/csv
* GET groups/{group_id}/csv
* GET groups/sample/csv

Message / pre defined information to be send
--------------------------------------------

### Fax Documents
* POST messages/documents
* GET messages/documents
* GET messages/documents/{document_id}
* PUT messages/documents/{document_id}
* DELETE messages/documents/{document_id}
* PUT messages/documents/{document_id}/media
* GET messages/documents/{document_id}/media

### Voice Recordings
* POST messages/recordings
* GET messages/recordings
* GET messages/recordings/{recording_id}
* PUT messages/recordings/{recording_id}
* DELETE messages/recordings/{recording_id}
* PUT messages/recordings/{recording_id}/media
* GET messages/recordings/{recording_id}/media

### Email templates
* POST messages/templates
* GET messages/templates
* GET messages/templates/{template_id}
* PUT messages/templates/{template_id}
* DELETE messages/templates/{template_id}
* POST messages/templates/{template_id}/media
* GET messages/templates/{template_id}/media

### SMS Text Message
* POST messages/texts
* GET messages/texts
* GET messages/texts/{text_id}
* PUT messages/texts/{text_id}
* DELETE messages/texts/{text_id}


Programs
--------

### General program related function
* POST programs (dummy, instead use a relevant program)
* GET programs
* GET programs/{program_id}
* PUT programs/{program_id}
* DELETE programs/{program_id}
* POST programs/{program_id}/transmissions
* GET programs/{program_id}/transmissions

### Email to Fax program
* POST programs/emailtofax

### Fax to Email program
* POST programs/faxtoemail

### Receive Email program
* POST programs/receiveemail

### Receive FAX program
* POST programs/receivefax

### Receive SMS program
* POST programs/receivesms

### Send Email program
* POST programs/sendemail

### Send FAX program
* POST programs/sendfax

### Send SMS program
* POST programs/sendsms

### Voice Call with pre recorded message
* POST programs/voicemessage

### Voice Call with tts
* POST programs/voicetts

### Custom Forward program
* POST programs/forward

### Custom IVR program
* POST programs/ivr

Transmission - the actual call or action
----------------------------------------

### create call request / dial / send message
* POST transmissions (dummy, instead use programs/{program_id}/transmissions)
* GET transmissions
* GET transmissions/{transmission_id}
* DELETE transmissions/{transmission_id}
* POST transmissions/{transmission_id}/send
* POST transmissions/{transmission_id}/retry
* GET transmissions/{transmission_id}/clone
* PUT transmissions/{transmission_id}/schedule
* DELETE transmissions/{transmission_id}/schedule

### Reports
* GET transmissions/{transmission_id}/status
* GET transmissions/{transmission_id}/detail
* GET transmissions/{transmission_id}/result
* GET spools/{spool_id}/status
* GET spools/{spool_id}/result

Campaigns : Communication in bulk
---------------------------------

### Campaigns
* POST campaigns
* GET campaigns
* PUT campaigns/{campaign_id}
* DELETE campaigns/{campaign_id}
* PUT campaigns/{campaign_id}/start
* PUT campaigns/{campaign_id}/stop
* PUT campaigns/{campaign_id}/start/schedule
* PUT campaigns/{campaign_id}/stop/schedule
* DELETE campaigns/{campaign_id}/start/schedule
* DELETE campaigns/{campaign_id}/stop/schedule
* DELETE campaigns/{campaign_id}/schedule

Account / Email / DID / Extension
---------------------------------

### Accounts
* POST accounts
* GET accounts
* GET accounts/{account_id}
* GET accounts/{account_id}/provisioning
* PUT accounts/{account_id}
* DELETE accounts/{account_id}
* PUT /accounts/{account_id}/programs/{program\_name}
* DELETE /accounts/{account_id}/programs
* DELETE /accounts/{account_id}/programs/{program\_name}
* PUT /accounts/{account_id}/users/{user\_id}
* DELETE /accounts/{account_id}/users
* GET /accounts/{account_id}/settings/{name}
* PUT /accounts/{account_id}/settings/{name}
* DELETE /accounts/{account_id}/settings/{name}

### DIDs
* POST dids
* GET dids

### Extension
* POST extensions
* GET extensions

User Management
---------------
* POST users
* GET users
* GET users/{user_id}
* PUT users/{user_id}
* PUT users/{user_id}/password
* DELETE users/{user_id}
* PUT users/{user_id}/permissions/{permission\_id}
* DELETE users/{user_id}/permissions/{permission\_id}
* PUT users/{user_id}/roles/{role\_id}
* DELETE users/{user_id}/roles/{role\_id}
* GET /users/{user_id}/accounts

Trunk / Termination Providers APIs
----------------------------------
* POST providers
* GET providers
* GET providers/{provider_id}
* PUT providers/{provider_id}
* DELETE providers/{provider_id}
