Initializing a Program
----------------------
Each program must be initialized before its execution. after initialization it can be used by one or more transmission over and over again. Before starting we have to make sure that we have all the required data in hand like message / document, destination address and source account

### preparing
1. client will create required messages
2. and will provide that message id to program while initiating it

```sequence
participant RestClient
participant Message
participant Program
participant Database
participant Dialplan

RestClient->Message:create a new message\n POST /messages/$message_type
Message->Database: save
Message->RestClient: message_id
RestClient->Program:create an instance of program\n POST /programs/$program_name
Program->Database: save
Program->Dialplan: deploy
Program->RestClient: program_id
```

### Program Deployment (dialplan)
While initiating a new program it is required to design its flow using a number of application and their internal links (actions). further Certain applications require a entry into system dialplan to listen inbound requests, i.e incoming call, sms or emails. following steps are automatically triggered when deploy method is called for newly created program.

1. A request for dialplan will be triggered from deploy function in certain program classes
2. Program will trigger all deploy function in all associated applications providing then a chance to save their dialplan if needed.
3. Concerned applications will use create a new instance of Dialplan class
4. Application will set all nessacerry parameters and pattern for dialplan
5. And will save that dialplan

```flow
deploy=>start: start
finish=>end: End

programScheme=>operation: Loop through 
all program assciated
applications
saveApplication=>operation: Save application
saveAction=>operation: Save application relations
deployApplication=>condition: if application 
have dialplan
saveDialplan=>operation: Create and save a new 
dialplan for application
lastApplication=>condition: if last applications
deploy->programScheme->saveApplication->saveAction->deployApplication(yes)->saveDialplan->lastApplication->finish

deployApplication(no)->lastApplication
lastApplication(yes)->finish
lastApplication(no)->programScheme

```

Using Program
-------------
To use program we have to create a transmission for that program

### Starting Transmission
1. client will create required contact
2. and will provide account_id and recently created contact_id to transmission_create which will return transmission_id of newly created transmission.
3. client will call send method for transmission while providing recent transmission_id
4. A new spool record will be created
5. And then program's execute function will be called

```sequence
participant RestClient
participant Transmission
participant Spool
participant Program
participant Application
participant Gateway

RestClient->Program:create a new transmission\n POST /programs/$program_id/transmissions
RestClient->Transmission:send / start transmission\n GET /transmissions/$transmission_id/send
Transmission->Spool:create a\n new spool
Transmission->Program:execute program
Program->Application:Load initial\n applications
Program->Application:Execute all initial\n applications
Application->Gateway: Send application\n data to gateway
```


### In Live Transmission
1. After processing each single application, gateway will request ictcore for further instructions
2. At ICTCore Core::process function will receive that request and after loading appropriate transmission and program, request will be forward to Program::process method
3. Program will load relevant application and will call Application::process method
4. Application will collect gateway request and will determine the result of execution
4. Based on application results program will search for next application
6. Matching application will be loaded and executed
7. Result of application execution will be returned to gateway

```flow
startRequest=>start: Start
finish=>end: End
requestGateway=>inputoutput: Gateway request received
for new instructions
CoreProcess=>operation: Core\:\:Process 
load tranmsission
load program
load application
ApplicationProcess=>operation: Collect application result
ApplicationMore=>condition: If there is pending
applications
ProgramSearch=>condition: if success
ApplicationAExecute=>operation: Execute application A
ApplicationBExecute=>operation: Execute application B
SendOutput=>inputoutput: Send output to gateway
GatewayExecute=>operation: execute application
startRequest->requestGateway->CoreProcess->ApplicationProcess->ApplicationMore(yes)->ProgramSearch->ProgramSearch(yes)->ApplicationAExecute->SendOutput
ProgramSearch(no)->ApplicationBExecute->SendOutput
ApplicationMore(no)->finish
```