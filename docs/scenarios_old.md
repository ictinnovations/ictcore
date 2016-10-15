SendFax Program
===============
To draw flow / sequence diagrams, we will take SendFax program as an example, In following we will try to draw a complete flow of SendFax program, from its initialization to its execution.


Initializing SendFax
--------------------
Each program must be initialized before its execution. after initialization it can be used over and over again. Before starting we have to make sure that we have required data in hand like message / document

### preparing
1. client will create required document
2. and will provide that document id to program while initiating it
3. After that program will be compiled on client request.

```sequence
participant RestClient
participant Message
participant Program
participant Database

RestClient->Message:create a new message
RestClient->Message:save()
Message->Database: save
Message->RestClient: message_id
RestClient->Program:create a new instance of program
RestClient->Program:save()
Program->Database: save
Program->RestClient: program_id
RestClient->Program:compile()
```

### compiling
1. A request for compilation will trigger compile function in program class
2. in compile function there is a list of steps, each steps can create a new instance of required application or can link two already instantiated applications.
3. program request to application to instantiate itself
4. After creating application program will try to create link between application use action instances.
5. steps not 3 and 4 will be repeated until there is no more application remaining in compile list.

```flow
compile=>start: Compile request (start)
finish=>end: End
loopApplication=>condition: All Application done
createApplication=>operation: Create Application
saveApplication=>operation: Save Application
createAction=>operation: Link two Applications
saveAction=>operation: Save link
compile->createApplication->saveApplication->createAction->saveAction->loopApplication->finish
loopApplication(no)->createApplication
loopApplication(yes)->finish
```

Using SendFax
-------------
To use send fax we have to create a transmission with program_id (We can get that program_id during SendFax initialization process)

### Starting Transmission
1. client will create required account
2. and contact
3. and will provide recently created account_id, contact_id and program_id to transmission while initiating it
4. Transmission will load all those objects
5. client will call send method on transmission object
6. Transmission will save its data
7. Will create a new spool id
8. And will kick program's execute function

```sequence
participant RestClient
participant Transmission
participant Spool
participant Program
participant Application
participant Gateway

RestClient->Transmission:create a new\n transmission
RestClient->Transmission:set(program_id)
RestClient->Transmission:set(account_id)
RestClient->Transmission:set(contact_id)
RestClient->Transmission:send
Transmission->Transmission:save()
Transmission->Spool:create a new spool
Spool->Spool:save()
Transmission->Program:execute program
Program->Application:Load initial\n applications
Program->Application:Execute all initial\n applications
Application->Gateway: Send application\n data to gateway
```

### In Live Transmission
1. After processing each single application, gateway will request ictcore for further instructions
2. at ICTCore spool object will receive that request and will forward it to application
3. Application will load all available actions
4. Will test each action with resulted data
5. Will collect matching action's Applications
6. All matching application will be loaded and executed
7. Result of application execution will be returned to gatewa

```flow
startRequest=>start: Start
finish=>end: End
requestGateway->operation: Gateway: request new application
readSpool->operation: Spool: read input from gateway
loadSpool->operation: Spool: load requested application
processApp->operation: Application: process input data
loadApp->operation: Application: load all actions
testApp->operation: Application: test actions
nextApp->operation: Application: load matching applications
execApp->operation: NewApp: Execute new application
outputApp->operation: NewApp: return result to gateway
executeGateway->operation: Gateway: execute data
loopApplication=>condition: All Application done
startRequest->requestGateway->readSpool->loadSpool->processApp->loadApp->testApp->nextApp->execApp->outputApp->executeGateway->loopApplication->finish
loopApplication(no)->requestGateway
loopApplication(yes)->finish
```
