ICTCore REST APIs Guide
=======================

Contact / pre defined destination number
----------------------------------------
* POST contacts
* GET contacts
* GET contacts/{contact_id}
* PUT contacts/{contact_id}
* DELETE contacts/{contact_id}

Group / pre defined for bulk system
----------------------------------------
* POST   groups
* GET    groups
* GET    groups/{group_id}
* PUT    groups/{group_id}
* DELETE groups/{group_id}
* GET    groups/{group_id}/export/contact.csv
* POST   groups/import/contact_group/{group_id}
* GET    groups/import/contact_csv/sample

Message / pre defined information to be send
--------------------------------------------

### Fax Documents
* POST messages/documents
* GET messages/documents
* GET messages/documents/{document_id}
* PUT messages/documents/{document_id}
* DELETE messages/documents/{document_id}
* GET messages/documents/{document_id}/download

### Voice Recordings
* POST messages/recordings
* GET messages/recordings
* GET messages/recordings/{recording_id}
* PUT messages/recordings/{recording_id}
* DELETE messages/recordings/{recording_id}
* GET messages/recordings/{recording_id}/download

### Email templates
* POST messages/templates
* GET messages/templates
* GET messages/templates/{template_id}
* PUT messages/templates/{template_id}
* DELETE messages/templates/{template_id}
* GET messages/templates/{template_id}/download

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

Campaign - the actual for bulk system
-----------------------------------------
cammpaign create for bulk process using system-level processes (deamon)

* POST   campaigns
* GET    campaigns
* PUT    campaigns/{campaign_id}
* DELETE campaigns/{campaign_id}
* GET    campaign/{campaign_id}/start
* GET    campaign/{campaign_id}/stop
* POST   campaigns/{campaign_id}/{action}/schedule
* DELETE campaign/{campaign_id}/schedule/cancel

Transmission - the actual call or action
----------------------------------------

### create call request / dial / send message
* POST transmissions (dummy, instead use programs/{program_id}/transmissions)
* GET transmissions
* GET transmissions/{transmission_id}
* DELETE transmissions/{transmission_id}
* GET transmissions/{transmission_id}/send ( or /call or /dial )
* GET transmissions/{transmission_id}/retry
* GET transmissions/{transmission_id}/clone
* PUT transmissions/{transmission_id}/schedule
* DELETE transmissions/{transmission_id}/schedule

### Reports
* GET transmissions/{transmission_id}/status
* GET transmissions/{transmission_id}/detail
* GET transmissions/{transmission_id}/result
* GET spools/{spool_id}/status
* GET spools/{spool_id}/result

Account / Email / DID / Extension
---------------------------------
* POST accounts
* GET accounts
* GET accounts/{account_id}
* PUT accounts/{account_id}
* DELETE accounts/{account_id}
* PUT /accounts/{account_id}/programs/{program\_name}
* DELETE /accounts/{account_id}/programs
* DELETE /accounts/{account_id}/programs/{program\_name}
* PUT /accounts/{account_id}/users/{user\_id}
* DELETE /accounts/{account_id}/users

User Management
---------------
* POST users
* GET users
* GET users/{user_id}
* PUT users/{user_id}
* DELETE users/{user_id}
* PUT users/{user_id}/permissions/{permission\_id}
* DELETE users/{user_id}/permissions/{permission\_id}
* PUT users/{user_id}/roles/{role\_id}
* DELETE users/{user_id}/roles/{role\_id}

Trunk / Termination Providers APIs
----------------------------------
* POST providers
* GET providers
* GET providers/{provider_id}
* PUT providers/{provider_id}
* DELETE providers/{provider_id}

Permission Management
---------------------
* POST permissions
* GET permissions

Role management
---------------
* POST roles
* GET roles
* GET roles/{role_id}
* PUT roles/{role_id}
* DELETE roles/{role_id}
* PUT roles/{role_id}/permissions/{permission_id}
* DELETE roles/{role_id}/permissions/{permission_id}