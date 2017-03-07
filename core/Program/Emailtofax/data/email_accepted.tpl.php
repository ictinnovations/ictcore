<?php
$template = array();

$template['subject'] = "Fax request received";
$template['attachment'] = NULL;
$template['body'] = <<<EOS
<p>
Dear [transmission:account:first_name] [transmission:account:last_name],
</p>
<p>
We have received a request to send fax from your email address [request:transmission:account:email].<br/>
Destination: [request:transmission:contact:phone]
</p>
<p>
Our system is processing it and we will inform you when done.
</p>

<p>
Thanks<br/>
-----------------------<br />
The [company:name] Team
</p>
EOS;
$template['body_alt'] = <<<EOS
Dear [transmission:account:first_name] [transmission:account:last_name],

We have received a request to send fax from your email address [request:transmission:account:email].
Destination: [request:transmission:contact:phone]

Our system is processing it and we will inform you when done.

Thanks
-----------------------
The [company:name] Team
EOS;
/* just an empty line, needed by EOS to maintain new line condition */