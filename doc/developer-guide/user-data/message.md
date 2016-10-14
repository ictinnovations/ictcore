Message Object
==============
Stored data / file holding information received or need to be delivered via some telecommunication related activity, for example Voice recording or Fax document etc..


Database / Tables
-----------------
Diffirent tables, one for each implemention, i.e

 * recording
 * document
 * text
 * template


Properties
----------
As mentioned above each implemention have diffirent table, so properties. following are some common one. for more details please check documention of other message implemention like recording and document etc ...

    'message_id',
    'name',

Methods
-------
Please check common.md from developer-guide directory, in addition to common methods message object may have following function

### get_download
