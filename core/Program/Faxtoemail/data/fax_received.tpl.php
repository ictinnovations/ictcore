<?php
$template = array();

$template['subject'] = "You have received a Fax";
$template['attachment'] = "[fax:document:pdf_file]";
$template['body'] = <<<EOS
<p>
Dear [transmission:account:first_name] [transmission:account:last_name],
</p>

<p>
A fax has been received at your account attached with this email. Following are the details:
</p>

<p>
Received at   : [fax:transmission:account:phone]<br />
Received from : [fax:transmission:contact:phone]<br />
Total pages   : [fax:document:pages]
</p>

<p>
Thanks<br/>
-----------------------<br />
The [company:name] Team
</p>
EOS;
$template['body_alt'] = <<<EOS
Dear [transmission:account:first_name] [transmission:account:last_name],

A fax has been received at your account attached with this email. Following are the details:

Received at   : [fax:transmission:account:phone]
Received from : [fax:transmission:contact:phone]
Total pages   : [fax:document:pages]

Thanks
-----------------------
The [company:name] Team
EOS;
/* just an empty line, needed by EOS to maintain new line condition */
