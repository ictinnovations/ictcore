<?php
$template = array();
$template['subject'] = "Email processing failed";
$template['attachment'] = NULL;
$template['body'] = <<<EOS

<table style="margin:0px auto;border-spacing:0px;width:52%">
<tbody>
<tr>
<td style="padding:20px 0px 17px 50px;" bgcolor="#333333"></td>
</tr>
<tr>
<td style="border:1px solid #eeeeef; padding:35px 50px;font-size:13px;line-height:20px;font-family:Helvetica,sans-serif;">
<p>

Hi [transmission:account:first_name] [transmission:account:last_name],<br><br>

We are unable to process your email.<br>
Please check if you have followed all the instructions.<br><br>

Error: [request:transmission:result:error:data]<br><br>

Thank you for using [company:name].<br>
Did you know that you can view your faxes online at [company:name]. Need Help? Visit our website<br><br>

<b>Best Regards</b></p>
<p style="color:#666"><b>[company:name] Team</b></p>
<p style="color:#666">[company:name] - Online Faxing </p>
<p style="color:#666">Site : <a href="http://ictcore.org">ictcore.org</a></p>
<br><p style="text-align:center;color:#666">ICTCore is developed by <a href="http://ictinnovations.com/">ICT Innovations</a></p>
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

We are unable to process your email.
Please check if you have followed all the instructions.

Error: [request:transmission:result:error:data]

Thanks
-----------------------
The [company:name] Team
?>
EOS;
/* just an empty line, needed by EOS to maintain new line condition */
