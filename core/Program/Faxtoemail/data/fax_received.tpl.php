<?php
$template = array();

$template['subject'] = "You have received a Fax";
$template['attachment'] = "[fax:document:file_name]";
$template['body'] = <<<EOS
<p>
Dear [account:first_name] [account:last_name], 
</p>

<p>
A fax has been received at your account attached with this email. Following are the details:
</p>

<p>
Received at   : [fax:account:phone]
Received from : [fax:contact:phone]
Total pages   : [fax:document:pages]
</p>

<p>
Thanks<br/>
-----------------------<br />
The [company:first_name] [company:last_name] Team
</p>
EOS;
$template['body_alt'] = <<<EOS
Dear [account:first_name] [account:last_name], 

A fax has been received at your account attached with this email. Following are the details:

Received at   : [fax:account:phone]
Received from : [fax:contact:phone]
Total pages   : [fax:document:pages]

Thanks
-----------------------
The [company:first_name] [company:last_name] Team
EOS;
/* just an empty line, needed by EOS to maintain new line condition */
