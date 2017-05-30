=====================
nethserver-squidguard
=====================

The package configures ufdbGuard, a URL filter for squid.

Features of ufdbGuard:

-  3-4 times faster than squidGuard
-  a multithreaded daemon with one copy of the database in memory
-  detects HTTPS proxy tunnels
-  detects SSH-based tunnels
-  blocks HTTPS for URLs without FQDN
-  blocks HTTPS for sites without a properly signed SSL certificate
-  uses in-memory databases
-  enforce the SafeSearch feature on Google and other search engines
-  a test mode (-T option) allows you to test a URL filter database without actually blocking sites


Inner workings
==============

For each request, squid sends the URL to one ``ufdbgclient`` redirector (which runs as user squid),
which in turn asks to the ``ufdb`` daemon (which runs as user ufdb).


Known limitations
=================

- Transparent URL filtering on HTTPS websites can only block whole domains, because ufdbGuard can only receive
the domain name, not the full URL

- Redirected HTTPS show an error instead of the block page


Database
========

Prop ``squidguard`` contains all settings.

Prop ``ufdb`` is a service which is enabled/disabled according to the status prop of the squidguard
(see nethserver-squidguard-ufdb-status action).


Troubleshooting
===============

Some commands: ::

  echo "http://bit.ly 10.10.0.1/ - - GET" | /usr/sbin/ufdbgclient -d
  echo "http://bit.ly 10.10.0.1/ user@mydomain.com - GET" | /usr/sbin/ufdbgclient -d
  /etc/init.d/ufdb testconfig 2>&1 | grep FATAL

Logfiles: ::

  /var/ufdbguard/logs/ufdbguardd.log
  
