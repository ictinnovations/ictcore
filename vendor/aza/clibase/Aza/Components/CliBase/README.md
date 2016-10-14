AzaCliBase
==========

Anizoptera CMF component with basic functionality and helper methods for CLI and Daemon applications (forks, libevent, etc..).

https://github.com/Anizoptera/AzaCliBase

[![Build Status][TravisImage]][Travis]


Table of Contents
-----------------

1. [Introduction](#introduction)
2. [Requirements](#requirements)
3. [Optional requirements](#optional-requirements)
4. [Installation](#installation)
5. [Credits](#credits)
6. [License](#license)
7. [Links](#links)


Introduction
------------

Provides convenient API for commonly needed tasks in CLI or Daemon applications.

**Some features:**

* Detaching process from the controlling terminal;
* Fork wrapper (with libevent base reinitializing if needed);
* Signals and exit codes reference;
* Signals hadling and simple waiting (with pcntl);
* Get current tty width in columns;
* Get running command by PID;
* Kill process tree;
* Change process title;
*  ... other;


Requirements
------------

* PHP 5.3.3 (or later);
* Unix system;
* [pcntl](http://php.net/pcntl);
* [posix](http://php.net/posix);


Optional requirements
---------------------

* [proctitle](http://php.net/proctitle) extension to change process title;
* [aza/libevent](https://packagist.org/packages/aza/libevent) and [libevent](http://php.net/libevent) extension to store one main event base for application;


Installation
------------

The recommended way to install AzaCliBase is [through composer](http://getcomposer.org).
You can see [package information on Packagist][ComposerPackage].

```JSON
{
	"require": {
		"aza/clibase": "~1.0"
	}
}
```


Credits
-------

AzaCliBase is a part of [Anizoptera CMF][], written by [Amal Samally][] (amal.samally at gmail.com) and [AzaGroup][] team.


License
-------

Released under the [MIT](LICENSE.md) license.


Links
-----

* [Composer package][ComposerPackage]
* [Last build on the Travis CI][Travis]
* [Project profile on the Ohloh](https://www.ohloh.net/p/AzaCliBase)
* Other Anizoptera CMF components on the [GitHub][Anizoptera CMF] / [Packagist](https://packagist.org/packages/aza)
* (RU) [AzaGroup team blog][AzaGroup]



[Anizoptera CMF]:  https://github.com/Anizoptera
[Amal Samally]:    http://azagroup.ru/about/#amal
[AzaGroup]:        http://azagroup.ru/
[ComposerPackage]: https://packagist.org/packages/aza/clibase
[TravisImage]:     https://secure.travis-ci.org/Anizoptera/AzaCliBase.png?branch=master
[Travis]:          http://travis-ci.org/Anizoptera/AzaCliBase
