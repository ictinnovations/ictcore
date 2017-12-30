<?php
$template = array();

$template['subject'] = "Fax request received";
$template['attachment'] = NULL;
$template['body'] = <<<EOS

<table style="margin:0px auto;border-spacing:0px;width:52%">
<tbody>
<tr>
<td align="center">
<a href="http://ictfax.org"><img src="http://ictfax.org/sites/default/files/ictfax_0.png" style="color:rgb(0,153,153);" height="60"><a><br>
</td>
</tr>
<tr>
<td style="padding:20px 0px 17px 50px;" bgcolor="#333333"></td>
</tr>
<tr>
<td style="border:1px solid #eeeeef; padding:35px 50px;font-size:13px;line-height:20px;font-family:Helvetica,sans-serif;">
<p>

Hi [transmission:account:first_name] [transmission:account:last_name],<br><br>

We have received a request to send fax from your email address <b>[request:transmission:account:email].</b><br>

Destination: <b>[request:transmission:contact:phone]</b><br>

Our system is currently processing your fax and we will inform you once it has been sent.<br><br>

Thank you for using [company:name].<br>

Did you know that you can view your faxes online at [company:name]. Need Help? Please visit our website<br><br>

<b>Best Regards</b></p>
<p style="color:#666"><b>[company:name] Team</b></p>
<p style="color:#666">[company:name] - Online Faxing </p>
<p style="color:#666">Email : support@ictinnovations.com </p>
<p style="color:#666">Site : <a href="http://ictfax.org">www.ictfax.org</a></p>
<br><p style="text-align:center;color:#666">ICTFAX is developed by <a href="http://ictinnovations.com/">ICT Innovations</a></p>
</td>
</tr>
<tr>
<td>

</td>
</tr>
<tr>
<td style="background-color:rgb(57,54,51);border-collapse:collapse;background-repeat:initial initial">&nbsp;</td>
</tr>
</tbody>
</table>
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
