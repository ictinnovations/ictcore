### Spool
A sub object for transmission, we can say it represent and hold method related to a single attempt of transmission.

*  __construct($spool_id = NULL)
create new instance of spool, load existing spool if $spool_id is provided

*  validate($field, $value)
before saving, validate current instance

*  delete()
delete this spool entry

*  get($field, $value)
get value of given field of spool object

*  set($field, $value)
set value of a given field of spool object

*  save()
save this spool object. If new object then add new record in database, if existing object then update record.

