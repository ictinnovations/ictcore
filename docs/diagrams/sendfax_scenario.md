SendFax Program
===============
To draw flow / sequence diagrams, we will take SendFax program as an example, In following we will try to draw a complete flow of SendFax program, from its initialization to its execution.


Initializing SendFax
--------------------
Each program must be initialized before its execution. after initialization it can be used over and over again. Before starting we have to make sure that we have required data in hand like message / document

### preparing
1. client will create required document
2. and will provide that document id to program while initiating it
3. After that program will be deployed on client request.

```sequence
participant RestClient
participant Message
participant Program
participant Database
participant Dialplan

RestClient->Message:create a new document\n POST /documents
Message->Database: save
Message->RestClient: message_id
RestClient->Program:create new program instance\n POST /programs/sendfax
Program->Database: save
Program->Dialplan:deploy()
Program->RestClient: program_id
```

### deployment
1. A request for deployment will trigger deploy function in program class
2. in deploy function there is a list of steps, each steps can create a new instance of required application or can link two already instantiated applications.
3. program request to application to instantiate themselves
4. After creating application program will try to create link between application use action instances.
5. steps not 3 and 4 will be repeated until there is no more application remaining in compile list.

```flow
deploy=>start: Deploy request (start)
finish=>end: End
loopApplication=>condition: All Application done
createApplication=>operation: Create Application
saveApplication=>operation: Save Application
deployApplication=>condition: If dialplan needed
saveDialplan=>operation: Save Dialplan
createAction=>operation: Create link between current and
previous Applications
saveAction=>operation: Save link
deploy->createApplication->saveApplication->deployApplication(yes)->saveDialplan->createAction->saveAction->loopApplication(yes)->finish
deployApplication(no)->createAction
loopApplication(no)->createApplication
```

Using SendFax
-------------
To use send fax we have to create a transmission with program_id (We can get that program_id during SendFax initialization process)

### Starting Transmission
1. client will create required contact
2. and will use recently created program_id, contact_id and account_id to create a new transmission
4. Later client can call send method on transmission object
5. Transmission will create a new spool record
6. And will kick program's execute function

```sequence
participant RestClient
participant Transmission
participant Spool
participant Program
participant Application
participant Gateway

RestClient->Transmission:create a new transmission\n POST /programs/$program_id/transmissions
Transmission->Transmission:save()
Transmission->RestClient:transmission_id
RestClient->Transmission:send / start transmission\n GET /transmissions/$transmission_id/send
Transmission->Spool:create a\n new spool
Spool->Spool:save()
Transmission->Program:execute program
Program->Application:Load initial\n application
Program->Application:Execute initial\n application
Application->Gateway: Send application\n data to gateway
```
