ICTCore REST APIs Guide
=======================

Contact / pre defined destination number
----------------------------------------
* contact/create
* contact/list
* contact/{contact_id}
* contact/{contact_id}/update
* contact/{contact_id}/delete

Message / pre defined information to be send
--------------------------------------------

### Fax Documents
* message/document/create
* message/document/list
* message/document/{document_id}
* message/document/{document_id}/update
* message/document/{document_id}/delete
* message/document/{document_id}/download

### Voice Recordings
* message/recording/create
* message/recording/list
* message/recording/{recording_id}
* message/recording/{recording_id}/update
* message/recording/{recording_id}/delete
* message/recording/{recording_id}/download

### Email templates
* message/template/create
* message/template/list
* message/template/{template_id}
* message/template/{template_id}/update
* message/template/{template_id}/delete
* message/template/{template_id}/download

### SMS Text Message
* message/text/create
* message/text/list
* message/text/{text_id}
* message/text/{text_id}/update
* message/text/{text_id}/delete


Programs
--------

### General program related function
* program/create (dummy, instead use create method from relevant program)
* program/list
* program/{program_id}/delete
* program/{program_id}/transmission

### Email to Fax program
* program/create/emailtofax

### Fax to Email program
* program/create/faxtoemail

### Receive Email program
* program/create/receiveemail

### Receive FAX program
* program/create/receivefax

### Receive SMS program
* program/create/receivesms

### Send Email program
* program/create/sendemail

### Send FAX program
* program/create/sendfax

### Send SMS program
* program/create/sendsms
s
### Voice Call with pre recorded message
* program/create/voicemessage

### General program related function
* program/list
* program/{program_id}/delete

Transmission - the actual call or action
----------------------------------------

### create call request / dial / send message
* transmission/create (dummy, instead use program/{program_id}/transmission)
* transmission/list
* transmission/{transmission_id}/send ( or /call or /dial )
* transmission/{transmission_id}/schedule
* transmission/{transmission_id}/schedule/cancel
* transmission/{transmission_id}/retry
* transmission/{transmission_id}/clone

### Reports
* transmission/{transmission_id}/status
* transmission/{transmission_id}/detail
* transmission/{transmission_id}/result
* spool/{spool_id}/status
* spool/{spool_id}/result


Account / Email / DID / Extension
---------------------------------
* account/create
* account/list
* account/{account_id}
* account/{account_id}/update
* account/{account_id}/delete
* account/{account_id}/subscribe/{program\_id}
* account/{account_id}/unsubscribe/{program\_id}
* account/{account_id}/unsubscribe/all
* account/{account_id}/associate/{user\_id}
* account/{account_id}/dissociate

User Management
---------------
* user/create
* user/list
* user/{user_id}
* user/{user_id}/update
* user/{user_id}/delete
* user/{user_id}/assign/{role\_id}
* user/{user_id}/unassign/{role\_id}
* user/{user_id}/allow/{permission\_id}
* user/{user_id}/disallow/{permission\_id}

Trunk / Termination Providers APIs
----------------------------------
* provider/create
* provider/list
* provider/{provider_id}
* provider/{provider_id}/update
* provider/{provider_id}/delete
