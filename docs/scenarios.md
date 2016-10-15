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

RestClient->Message:create a new message
RestClient->Message:save()
Message->Database: save
Message->RestClient: message_id
RestClient->Program:create a new instance of program
RestClient->Program:save()
Program->Database: save
Program->Database: deploy
Program->RestClient: program_id
```

Using Program
-------------
To use program we have to create a transmission for that program

### Starting Transmission
1. client will create required contact
2. and will provide account_id and recently created contact_id to transmission_create which will return transmission_id of newly created transmission.
4. client will call send method for transmission while providing recent transmission_id
5. A new spool recording will be created
6. And then program's execute function will be called

```sequence
participant RestClient
participant Transmission
participant Spool
participant Program
participant Application
participant Gateway

RestClient->Program:create a new transmission
RestClient->Transmission:send
Transmission->Spool:create a\n new spool
Transmission->Program:execute program
Program->Application:Load initial\n applications
Program->Application:Execute all initial\n applications
Application->Gateway: Send application\n data to gateway
```

### In Live Transmission
1. After processing each single application, gateway will request ictcore for further instructions
2. at ICTCore process function will receive that request and will forward it to program
3. Program will load given application and will call its process function
4. Application process function will check the input and will set results accordingly
5. All assocated actions will be loaded for that application
6. Based on application result matching action will be selected
7. Program will load next application using selected action
8. Next application will be executed
9. Result of application execution will be returned to gateway

```flow
startRequest=>start: Start

finish=>end: End

requestGateway=>operation: Gateway:
request new application

readCore=>inputoutput: Core:
read input from gateway

loadTransmission=>operation: Core:
load related Spool and Transmission

loadProgram=>operation: Core:
load related Program

processProgram=>operation: Program:
process input data

loadApplication=>operation: Program:
load requested application

processApplication=>operation: Application:
process input data

resultApplication=>operation: Application:
prepare application results

loadAction=>operation: Application:
load all actions

testAction=>operation: Application:
test for matching actions

nextApplication=>operation: Application:
load next action / application

executeApplication=>operation: NextApplication:
Execute new application

outputApplication=>operation: NextApplication:
prepare output data

executeGateway=>inputoutput: Gateway:
send application data to gateway

loopApplication=>condition: All Application done

startRequest->requestGateway->readCore->loadTransmission->loadProgram->processProgram->loadApplication->processApplication->resultApplication->loadAction->testAction->nextApplication->executeApplication->outputApplication->executeGateway->loopApplication->finish

loopApplication(no)->requestGateway
loopApplication(yes)->finish
```


Program Dialplan
----------------
After initiating a new program sometime it is required to deploy it to listen inbound requests, i.e incomming call, sms or emails. following steps are automatically trigger when save method is called for newly created program.

### dialplan
1. A request for dialplan will be triggered from deploy function in certain program classes
2. Program will trigger all deploy function in all assocated application giveing then a chance to save their dialplan if needed.
3. Concerned application will use create a new instance of Dialplan class
4. Application will set all nessacerry parameters and pattern for dialplan
5. And will save that dialplan

```flow
deploy=>start: Deploy request (start)

finish=>end: End

loopApplication=>condition: Program:
All Application done

loadApplication=>operation: Program:
Load Application

deployApplication=>operation: Program:
Call application deply function

newDialplan=>operation: Application:
create new dialplan instance

setDialplan=>operation: Application:
set dialplan parameters and pattern

saveDialplan=>operation: Dialplan:
save dialplan

deploy->loadApplication->deployApplication->newDialplan->setDialplan->saveDialplan->loopApplication->finish

loopApplication(no)->loadApplication
loopApplication(yes)->finish
```
