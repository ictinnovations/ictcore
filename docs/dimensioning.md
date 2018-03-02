ICTCore Dimensioning
====================
We have tested ICTCore on 4 CPU / 8 GB RAM / SSD HD instance and have collected following results

Note: before testing it is required to increase file limit for freeswitch, which can be done by including following line in /lib/systemd/system/freeswitch.service

    LimitNOFILE=100000
    LimitNPROC=60000

With single campaign
--------------------
A single campaign has produced 18 call per seconds which resulted in 400 active calls while tested with a message having 24 seconds duration. it mean system will be able to produce 800 concurrent calls if call duration will be 60 seconds

* cps: 18
* concurrent calls: 800


With multiple campaigns
-----------------------
Multiple campaigns have produced 24 call per seconds which resulted in 600 active calls while tested with a message having 24 seconds duration. it mean system will be able to produce 1200 concurrent calls if call duration will be 60 seconds

* cps: 26
* concurrent calls: 1200


Bottlenecks
-----------

We have identified following bottlenecks in our testing

* Database server (MySQL)
* Freeswitch dialplan (lua script)

Recommendations
---------------
To further improve ICTCore performance it is recommended to 

### Freeswitch dialplan
* Use multiple and distributed Freeswitch instances
* Replace lua scripts with something faster, or rewrite those script to improve performance

### Database server
* Use SSD hard-disks
* Use a separate server for database (MySQL) hosting
* For larger setups database load balancer can be used
* Avoid database queries where ever possible

