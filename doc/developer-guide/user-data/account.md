Account Object
==============
Account will hold users/client contact information for all type of services like phone number for voice and fax, email for email etc... Each user can have multiple account, think it like DIDs or extension. account can be used as source for outbound, and as destination for inbound communication. 

TODO: only admin should be able to create new accounts.

Database / Tables
-----------------
 * account

### related table
 * usr

Properties
----------
'account_id',
'username',
'passwd',
'passwd_pin',
'first_name',
'last_name',
'phone',
'email',
'address',
'active'

Methods
-------
Please check common.md from developer-guide directory

