<?php
$template = <<<EOS
<table style="margin:0px auto;border-spacing:0px;width:52%">
<tbody>
<tr>
<td style="padding:20px 0px 17px 50px;" bgcolor="#333333"></td>
</tr>
<tr>
<td style="border:1px solid #eeeeef; padding:35px 50px;font-size:13px;line-height:20px;font-family:Helvetica,sans-serif;">
<p> <b>Hi [first_name]</b> <br>
We recieve a request to reset the password for your account. <br>
To reset your password click the link below.<br>
<b>[link]</b>.<br>
</p>
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
return $template;
