<?php
$template = array();

$template['subject'] = "Email processing failed";
$template['attachment'] = NULL;
$template['body'] = <<<EOS
<p>
Dear [account:first_name] [account:last_name], 
</p>

<p>
We are unable to process your email.<br/>
Please check if you have followed all the instructions.<br/>
</p>
Error: [request:result:response]

<p>
Thanks<br/>
-----------------------<br />
The [company:first_name] [company:last_name] Team
</p>
?>
EOS;
$template['body_alt'] = <<<EOS
Dear [account:first_name] [account:last_name], 

We are unable to process your email.
Please check if you have followed all the instructions.

Error: [request:result:response]

Thanks
-----------------------
The [company:first_name] [company:last_name] Team
?>
EOS;
/* just an empty line, needed by EOS to maintain new line condition */