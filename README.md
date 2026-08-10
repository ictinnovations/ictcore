ICTCore - A Unified Communications Framework for CTI
====================================================

[![Documentation Status](https://app.readthedocs.org/projects/ictcore/badge/?version=latest)](https://ictcore.readthedocs.io/en/latest/)

**Documentation: [ictcore.readthedocs.io](https://ictcore.readthedocs.io/en/latest/)**

[ICTCore] core is open source unified communications framework for developers and integrators to rapidly develop ICT based applications using their existing development skills. By using ICTCore, developer can create communication based applications such as Auto attendant, Fax to Email, Click to Call etc.. they can program custom business logic that can control incoming and outgoing communication instances.

ICTCore having primary focus on integrated and automated communications. ICTCore goal is to be a common server-side software / back-end for CTI (computer telephony integration) related projects. Further as a framework it can be extended to develop new communication solution and services using user's existing development skills and infrastructure.

ICTCore is a PHP and Linux based service application which support multiple gateway interfaces like Freeswitch, Kannel and Sendmail further it offers inbound and outbound transmissions for voice, fax, sms and email. However main feature of ICTCore is its unified way of integration which allow different kind of services to interact with each other, for example a message can trigger a call or an inbound fax can be forwarded over email.

Following are few projects developed over ICTCore communications framework

* [ICTFax] open source fax server software.
* [ICTDialer]  open source auto dialer software.
* [ICTCRM]   open source CRM with unified communications. 


Features
--------

### Truly unified communications framework
ICTCore allow developers to use multiple communication methods / services like Call, FAX, Messaging or Email, plus it also allow them to integrate these services with each other, like SMS notification after each call or email to fax.

* ICTCore cover all major communication methods
* Allow integration between different kind of communication methods
* Uses unified terminology and share-able resources for all its services

### Scenario based automation
ICTCore have builtin support for few most commonly used scenarios, However [ICTCore] architecture allow developers to easily implement their own custom scenarios and requirements.

* Flexible programs and logic to allow custom scenario
* Uses one program per scenario approach
* Various builtin programs for common scenarios
* Complete activity and result log for reporting purpose
* APIs and developer guide to build new program for custom scenarios

### Easy to extend
ICTCore is build for developers, ICTCore allows developers to

* Introduce new program and scenarios or modify the existing one
* Add support for new software switch, pbx and gateways
* Develop completely new communication methods

### Ready to use
* REST APIs
* Third party termination / origination support
* RPM based installation


Install
-------

### Docker

`docker/Dockerfile` builds a container with Apache, PHP, FreeSWITCH and MariaDB
already wired together, so there is nothing else to install.

```
docker build -f docker/Dockerfile -t ictcore:dev .

docker run -d --name ictcore \
  -p 8080:80 \
  -p 5060:5060/tcp -p 5060:5060/udp \
  -p 16384-16484:16384-16484/udp \
  ictcore:dev
```

First boot takes about two minutes while the database is created and the schema
loads. After that the API answers on `http://localhost:8080/api/`. Publish the
RTP range as shown or your calls will connect with no audio.

If you want a finished product rather than the bare API, the
[ictfax](https://github.com/ictinnovations/ictfax) and
[ictdialer](https://github.com/ictinnovations/ictdialer) images build this same
stack and add a dashboard, and both are published ready to pull.

To point the container at your own database server see
[docker/README.md](docker/README.md).

### From packages

Currently ICTCore binaries are available for CentOs 7, 8 and RockyLinux 8, To install ICTCore you need a freshly installed server and then you can follow the instructions mentioned in following. If you are looking for source code you can find it at github [ICTCore: Open Source Unified Communications Framework](https://github.com/ictinnovations/ictcore)

1. First of all we need to install ict and epel repositories  

##### CentOs 7 Installation Guide   

```
    yum install -y https://service.ictinnovations.com/repo/7/ict-release-7-4.el7.centos.noarch.rpm  
    yum install -y https://files.freeswitch.org/repo/yum/centos-release/freeswitch-release-repo-0-1.noarch.rpm
    yum install -y epel-release  
```

2. Install ICTCore  

```
    yum update  
    yum install ictcore ictcore-voice ictcore-fax ictcore-email  
```

3. Create a new database and database user for ictcore

4. Initiate / populate newly created database using scripts from `/usr/ictcore/db/*`

5. Update `/etc/ictcore.conf` and `/etc/odbc.ini` for database access

6. Restart HTTP / Apache server

##### CentOs 8 / RockyLinux 8 Installation Guide   

Disable the current PHP version and enable required PHP from remi repo

```
yum module disable php:7.2
yum install dnf-utils http://rpms.remirepo.net/enterprise/remi-release-8.rpm
yum module enable php:remi-7.4
```

Install the Okey repository for FreeSWITCH from this link 

```
rpm -ivh http://repo.okay.com.mx/centos/8/x86_64/release/okay-release-1-5.el8.noarch.rpm
```

Install ICTCore packages

```
yum install ictcore ictcore-fax ictcore-email ictcore-voice ictcore-freeswitch
```

- Install FastCGI Process Manager 

```
yum install -y php php-fpm php-gd php-mysqlnd php-imap
```

Configure the document root in apache configuration file /etc/httpd/conf/httpd.conf

```
DocumentRoot "/usr/ictfax"
#
# Relax access to content within /var/www.
#
<Directory "/var/www">
    AllowOverride None
    # Allow open access:
    Require all granted
</Directory>

# Further relax access to the default document root:
<Directory "/usr/ictfax">
```

Update /etc/httpd/conf.modules.d/00-mpm.conf like following

```
#LoadModule mpm_prefork_module modules/mod_mpm_prefork.so
LoadModule mpm_event_module modules/mod_mpm_event.so
```

Change PHP_ADMIN_VALUE open_basedir line into from /etc/httpd/conf.d/ictcore.conf file

```
SetEnv PHP_ADMIN_VALUE "open_basedir = /usr/ictcore/:/usr/bin:/bin:/tmp/"
```

Install Imagic 

```
yum install -y ImageMagick ImageMagick-devel  
pecl install imagick  
echo "extension=imagick.so" > /etc/php.d/imagick.ini  
```

Install mcrypt 

```
yum install --enablerepo=epel php-devel php-pear libmcrypt libmcrypt-devel  

pecl install mcrypt  
echo 'extension=mcrypt.so' > /etc/php.d/mcrypt.ini 
```

Disable selinux and restart the services 

```
setenforce 0
service httpd restart
service php-fpm restart
```

Setup the ICTCore database 

```CREATE DATABASE ictfax;
USE ictfax;
GRANT ALL PRIVILEGES ON ictfax.* TO ictfaxuser@localhost IDENTIFIED BY 'plsChangeIt';
FLUSH PRIVILEGES;
source /usr/ictcore/db/database.sql;
source /usr/ictcore/db/email.sql;
source /usr/ictcore/db/fax.sql;
source /usr/ictcore/db/voice.sql;
source /usr/ictcore/db/data/role_user.sql;
source /usr/ictcore/db/data/role_admin.sql;
source /usr/ictcore/db/data/demo_users.sql;

```

Open the file /etc/ictcore.conf and find out the [db] section and replace user, password and database name in the following lines:

```
user = ictfaxuser
pass = plsChangeIt
name = ictfax
```

Configure EMAIL TO FAX / FAX TO EMAIL SERVICE (OPTIONAL)"  

```
echo "ictcore" >> /etc/mail/trusted-users
echo "apache" >> /etc/mail/trusted-users

echo "FAX_DOMAIN.COM" >> /etc/mail/local-host-names

echo '@FAX_DOMAIN.COM ictcore' >> /etc/mail/virtusertable

```

to apply email related changes

```
/etc/mail/make
```

Configure the email-2-fax services

```
cd /usr/ictcore/bin/sendmail
./email_to_fax
```

Restart sendmail service so changes can take affect

```
chkconfig sendmail on
service sendmail restart
```

In case if document not uploading then install "libtiff-tools" package 

```
yum install libtiff-tools -y
```

Getting started
---------------
Following is an example about sending fax by using ICTCore

```
    // prepare a program with fax document
    $faxProgram = new Sendfax();
    $faxProgram->file_name = '/some/pdf/file.pdf';
    $faxProgram->save();
    $faxProgram->compile();

    // create a transmission
    $contact_id = 12;
    $account_id = 1;
    $faxTransmission = $faxProgram->transmission_create($contact_id, $account_id);

    // schedule transmission 
    $faxTransmission->schedule(array('delay' => 3600)); // in seconds

    // or dispatch immediately
    $faxTransmission->send();
```

Get involved!
-------------
We believe in leveraging open source in telecommunications, providing a free platform for simple and advanced CTI applications. [ICTCore] was built by people like you, and we need your help to make ICTCore better! Why not participate in a useful project today? Please check docs folder to learn how to begin.

License
-------
The ICTCore is open-sourced software licensed under the [MPLv2 license](https://www.mozilla.org/en-US/MPL/2.0/).

Contact us
----------
Website: [ICTCore website](http://ictcore.org/)  
About us: [About Us :: ICT Innovations Pakistan](http://www.ictinnovations.com/about-ict-innovations)  
Contact us: [Contact Us :: ICT Innovations Pakistan](http://www.ictinnovations.com/contact)

[ICTCore]: https://www.ictcore.org/ "ICTCore Communictions framework"
[ICT Innovations]: https://www.ictinnovations.com/ "ICT Innovations leveraging open source in ICTs"
[ICTFax]: https://www.ictfax.org/ "open source Fax server software"
[ICTDialer]: https://github.com/ictinnovations/ictdialer/ "open source auto diler software"
[ICTCRM]: https://www.ictcrm.com/ "open source CRM with uified communications"
