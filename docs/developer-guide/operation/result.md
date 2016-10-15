Ressult Object
==============
This class is responsible to collect and save application activity or results in database, it save transmission data as per each spool


Database / Tables
-----------------
 * spool_result


Properties
----------
'spool_result_id',
'application_id',
'type',
'name',
'data',
'date_created',
'spool_id'

Methods
-------
Please check common.md from developer-guide directory, in addition to common methods message object may have following function

### search
to search and load result by giving spool or application ids etc ...

### token_get
expose results as tokens for upcomming applications and programs
