WebRTC configuration for ICTCore
================================
To enable WebRTC support over WSS (secure port) in freeswitch we have to install certificates. following is guide to setup free letsencrypt certificates for Freeswitch

Get a domain name
-----------------
We need a domain name to generate certificate, sub domains are also allowed. for example we have sip.example.com

Before starting with certificate generation please redirect your domain / sub domains to your freeswitch server and also remember to replace `sip.example.com` in following with your own domain or sub domain name

Install Letsencrypt
-------------------
Install certbod binaries 

yum install certbot python2-certbot-apache -y


Generate certificates
---------------------
Enter the following command and proceed with prompts

certbot --apache -d sip.example.com

Install certificates in Freeswitch
----------------------------------

cd /etc/letsencrypt/live/sip.example.com

echo '' > /etc/freeswitch/tls/wss.pem && cat cert.pem >> /etc/freeswitch/tls/wss.pem && cat privkey.pem >> /etc/freeswitch/tls/wss.pem && cat chain.pem >> /etc/freeswitch/tls/wss.pem

systemctl restart freeswitch.service

Cronjob to keep certificates upto date
--------------------------------------
Letsencrypt expire free certficates after three months, we can override this issue by a simple cronjob

echo "30 2 * * * root /usr/bin/certbot renew >> /var/log/le-renew.log" > /etc/cron.d/letsencrypt.conf
