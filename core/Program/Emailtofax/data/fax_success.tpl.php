<?php
$template = array();

$template['subject'] = "Fax delivered successfully";
$template['attachment'] = NULL;
$template['body'] = <<<EOS
<p>
Dear [account:first_name] [account:last_name], 
</p>

<p>
We have successfully sent your fax. Following are the details:
</p>
<p>
Destination: [fax:contact:phone]
Total pages: [fax:result:pages]
</p>

<p>
Thanks<br/>
-----------------------<br />
The [company:first_name] [company:last_name] Team
</p>
EOS;
$template['body_alt'] = <<<EOS
Dear [account:first_name] [account:last_name], 

We have successfully sent your fax. Following are the details:

Destination: [fax:contact:phone]
Total pages: [fax:result:pages]

Thanks
-----------------------
The [company:first_name] [company:last_name] Team
EOS;
/* just an empty line, needed by EOS to maintain new line condition */