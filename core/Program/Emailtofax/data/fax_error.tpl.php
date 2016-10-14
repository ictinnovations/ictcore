<?php
$template = array();

$template['subject'] = "Fax delivery failed";
$template['attachment'] = NULL;
$template['body'] = <<<EOS
<p>
Dear [account:first_name] [account:last_name], 
</p>

<p>
We have tried to send your fax to [fax:contact:phone] but unfortunately it failed.
</p>
Error: [fax:result:response]
<p>
Please try again later.
</p>

<p>
Thanks<br/>
-----------------------<br />
The [company:first_name] [company:last_name] Team
</p>
EOS;
$template['body_alt'] = <<<EOS
Dear [account:first_name] [account:last_name], 

We have tried to send your fax to [fax:contact:phone] but unfortunately it failed.

Error: [fax:result:response]

Please try again later.

Thanks
-----------------------
The [company:first_name] [company:last_name] Team
EOS;
/* just an empty line, needed by EOS to maintain new line condition */
