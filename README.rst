=====================
nethserver-squidguard
=====================

The package configures ufdbGuard, a URL filter for squid.



Database
--------

`squidguard`
`ufdb`


Troubleshooting
---------------

 echo "http://bit.ly 10.10.0.1/ - - GET" | /usr/sbin/ufdbgclient -d
 /etc/init.d/ufdb testconfig 2>&1 | grep FATAL
 
