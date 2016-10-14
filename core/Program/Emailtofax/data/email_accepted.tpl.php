<?php
$template = array();

$template['subject'] = "Fax request received";
$template['attachment'] = NULL;
$template['body'] = <<<EOS
<p>
Dear [account:first_name] [account:last_name], 
</p>
<p>
We have received a request to send fax from your email address [request:account:email].<br/>
Destination: [request:contact:phone]
</p>
<p>
Our system is processing it and we will inform you when done.
</p>

<p>
Thanks<br/>
-----------------------<br />
The [company:first_name] [company:last_name] Team
</p>
EOS;
$template['body_alt'] = <<<EOS
Dear [account:first_name] [account:last_name], 

We have received a request to send fax from your email address [request:account:email].
Destination: [request:contact:phone]

Our system is processing it and we will inform you when done.

Thanks
-----------------------
The [company:first_name] [company:last_name] Team
EOS;
/* just an empty line, needed by EOS to maintain new line condition */