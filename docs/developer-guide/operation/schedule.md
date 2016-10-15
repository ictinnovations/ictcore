Ressult Object
==============
Transmission and other task can be scheuduled by useing this interface


Database / Tables
-----------------
 * schedule


Properties
----------
'schedule_id',
'type',
'action',
'data',
'month',
'day',
'weekday',
'hour',
'minute',
'weight',
'is_recurring',
'last_run',
'expiry',
'account_id'

Methods
-------
Please check common.md from developer-guide directory, in addition to common methods message object may have following function

### search
to search and load result by giving spool or application ids etc ...

### token_get
expose results as tokens for upcomming applications and programs
