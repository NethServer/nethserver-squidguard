==================
Web content filter
==================

The web content filter is used to control web browsing and
site blocking using some variables such as keywords, IP
address, internal users, or evaluating the content of the web page,
or file extensions. With this tool it is possible for example to enable
access only on some desired sites (such as those of company interest)
while blocking all others.

The content filter is based on profiles.
A profile is composed by three parts:

* Who: host or user is accessing the web
* What: a filter composed by multiple allowed or blocked categories
* When: a time frame within the access is filtered

There is also a special profile which applies to any client
at any time.


General
=======

General configuration common to all tabs.

Enable filter
    Enable or disabled the filter.

Enable expression matching on URL
    Filter URLs using regular expressions.
    For example, block URLs containing the word *sex*.
    Not recommended: this type of filter can lead to false positives.

List of blocked file extension
    A comma separated list of file extensions blocked by the filter.

Global blacklist
   List of blocked sites or URLs, can be enabled or disabled for each filter.

Global whitelist
   List of allowed sites or URLs, can be enabled or disabled for each filter.


Profiles
========

A profile describe who can access contents within defined time frames.

Name
   Unique name identifier.

Who
   If the proxy is configured in authenticated mode, it can be:
   * a user
   * a group of user

   If the proxy is configured in any other mode, it can be:
   * a host
   * a group of host

What
   A filter previously created inside the filter tab, or the default filter.

When
   A time frame previously created inside the times tab.

Description
    Custom description (optional).


Filters
=======

A filter describe what kind of content is allowed or blocked.

Name
    Unique name identifier.

Description
    Custom description (optional).

Block access to web sites using IP address
    If enabled, clients can not access websites using the IP address, but only the host name.

Enable global blacklist
    Enable the domain/URL blacklist defined in the General tab.

Enable global whitelist
    Enable the domain/URL whitelist defined in the General tab.

Block file extensions
    Block all file extensions defined in the General tab.

Mode
    The web filter can work into two different ways:

    * Block all, allow selected content: selected categories are allowed, any other site is blocked
    * Allow all, block selected content: selected categories are blocked, any other site is allowed

Categories
    List of categories from blacklists configured inside Blacklist tab.
    It also contains all defined custom categories.

Times
=====

Define a list of time frames.

Name
    Unique name identifier.

Description
    Custom description (optional).

Days of week
    Select one ore more days of the week.

Start time
    A start time for the time frame.

End time
    An end time for the time frame.


Custom categories
=================

Custom categories can be used inside the Filter tab.

Name
    Unique name identifier.

Description
    Custom description (optional).

Domains
    A list of custom domains, one per line.


Blacklists
==========

The lists are downloaded once a day during the night.
Available lists are:

* Shalla (free for non-commercial use)
* UrlBlacklist.com (commercial)
* Université Toulouse (free)
* Custom: set a custom URL, the list must be in a format
  suitable for SquidGuard


.. raw:: html

   {{{INCLUDE NethServer_Module_ContentFilter_*.html}}}
