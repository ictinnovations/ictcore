Common methods between ICTCore classes
======================================
Following is a list of methods which are common in all ICTCore data-bound classes, they are responsible to fetch and save data in databases whenever it is required by system.


__construct
-----------
Any code related to instance creation of class, will be found here


search
------
In case object / instance systematic id is unknown, this function can be used to locate it


token_get
---------
Expose concerned classes properties as token


load
----
While creating instance of class with previously save record, this function can be called ( from user or automatically by __construct ) to load data from database. and after that it is responsible to populate object variables with fetched data.


validate
--------
Before saving data this function will be called to verify if everything is good, i.e if we are ready to save.


delete
------
Clear all previously saved data from database, related to current instance.


Getter and Setters
------------------
Read and set object properties, any function prefix with 'get_' or 'set_' followed by field name will be called accordingly by these functions

save
----
Save current instance status ( all saveable properties and values ) into corresponding table in database
