### Gateway
Telecommunication engine / backend like Asterisk, Freeswitch, Sendmail etc .. currently there are two gateway available int ICTCore 

1. Freeswitch
2. Sendmail

Each gateway interface has following APIs

* __construct($user, $pass, $host, $port)
create a new instance of gateway object

* capabilities()
return list of service supported by current gateway

* is_supported($service)
confirm if a specific service is supported by gateway

* connect()
set connection handle of the service

* dissconnect()
disconnect handle of the service

* send($command)
send given command to the gateway

* outbound($destination, $message)
originate outgoing call

* template_message($service_flag)
return a message string template, i.e how user should write an message

* template_destination($service_flag)
return a dial string template, i.e how user should write an dialstring

* template_domain()
return a domain template, i.e how user should write an domain

* template_provider()
return a provider template, i.e how user should write an trunk name

* template_extension()
return a extension template, i.e how user should write an extension name

* config_save()
save gateway configuration

* reload()
refresh gateway, reload gateway configuration

