<?php
$template = array();

$template['subject'] = "Fax delivery failed";
$template['attachment'] = NULL;
$template['body'] = <<<EOS
<p>
Dear [transmission:account:first_name] [transmission:account:last_name],
</p>

<p>
We have tried to send your fax to [fax:transmission:contact:phone] but unfortunately it failed.
</p>
Error: [fax:program:result:response]
<p>
Please try again later.
</p>

<p>
Thanks<br/>
-----------------------<br />
The [company:name] Team
</p>
EOS;
$template['body_alt'] = <<<EOS
Dear [transmission:account:first_name] [transmission:account:last_name],

We have tried to send your fax to [fax:transmission:contact:phone] but unfortunately it failed.

Error: [fax:program:result:response]

Please try again later.

Thanks
-----------------------
The [company:name] Team
EOS;
/* just an empty line, needed by EOS to maintain new line condition */
