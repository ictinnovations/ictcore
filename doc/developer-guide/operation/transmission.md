### Transmission
A Transmission is a single communication event, like a successful or failed call or email delivery. regardless how many attempts are made to achieve that.

*  __construct($transmission_id = NULL)
create new instance of transmission, load existing transmission if $transmission_id is provided

*  validate($field, $value)
before saving, validate current instance

*  delete()
delete this transmission

*  get($field)
get value of given field of transmission object

*  set($field, $value)
set value of a given field of transmission object

*  save()
save this transmission object. If new object then add new record in database, if existing object then update record.

*  retry_all()
retry sending all transmissions that are pending, pending retry or failed previously but their retry limit is not reached.

*  retry()
retry sending this transmission only if retry limit is not reached. 

*  send()
send a transmission for the first time.
