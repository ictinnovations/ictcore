<?php
$template = array();

$template['subject'] = "Fax delivered successfully";
$template['attachment'] = NULL;
$template['body'] = <<<EOS
<p>
Dear [transmission:account:first_name] [transmission:account:last_name],
</p>

<p>
We have successfully sent your fax. Following are the details:
</p>
<p>
Destination: [fax:transmission:contact:phone]<br />
Total pages: [fax:transmission:result:pages:data]
</p>

<p>
Thanks<br/>
-----------------------<br />
The [company:name] Team
</p>
EOS;
$template['body_alt'] = <<<EOS
Dear [transmission:account:first_name] [transmission:account:last_name],

We have successfully sent your fax. Following are the details:

Destination: [fax:transmission:contact:phone]
Total pages: [fax:transmission:result:pages:data]

Thanks
-----------------------
The [company:name] Team
EOS;
/* just an empty line, needed by EOS to maintain new line condition */