### Application
Application class will contain the various communications related application logic that can be used by programmers to develop high level applications.

TODO: list application functions here

*   fax_received($destination, $file_path, $text, $source)
This application receives an incoming fax. It creates a new transmission object and then sets necessary information for this inbound transmission such as direction, service_flag, account and contact.

*   send_fax($destination, $file_path, $text, $source)
This application sends a fax to its outbound destination. After validating destination and fax file path, this application sets necessary information in transmission object such as direction, service flag, origin and account. It also sets the destination phone number and fax file in the transmission object. After that, it calls the sends method of transmission object to send fax.

*   send_fax_done($destination, $file_path, $text, $source)
This application sends an email to the account who sent a fax at its required destination. This email informs about the delivery of fax. This is an inbound communication. In this application, the account_id is of the account who sent fax and contact_id is the company or ICTFAX server who is the sender of the email.

*   send_email($destination, $subject, $body, $attachment, $source)
This application sends an email to the $destination object.  If destination is a valid object, then a new transmission is created. Service flag, direction, and destination email is set. Then message is prepared by setting the subject, body and attachment. And finally, the email is sent.

