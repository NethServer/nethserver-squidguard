Summary: NethServer squidGuard configuration
Name: nethserver-squidguard
Version: 1.9.2
Release: 1%{?dist}
License: GPL
URL: %{url_prefix}/%{name} 
Source0: %{name}-%{version}.tar.gz
BuildArch: noarch

Requires: nethserver-httpd, nethserver-squid >= 1.3.0
Requires: ufdbGuard

Obsoletes: squidGuard <= 1.4

BuildRequires: perl
BuildRequires: nethserver-devtools 

%description
NethServer ufdbGuard (squidGuard) configuration

%prep
%setup

%build
%{makedocs}
perl createlinks

%install
rm -rf %{buildroot}
(cd root; find . -depth -print | cpio -dump %{buildroot})
mkdir %{buildroot}/var/squidGuard/blacklists/cache.execlists
%{genfilelist} %{buildroot} > %{name}-%{version}-filelist  \
--dir /var/squidGuard/blacklists/custom/blacklist 'attr(0755,squid,squid)' \
--dir /var/squidGuard/blacklists/custom/whitelist 'attr(0755,squid,squid)' \
--dir /var/squidGuard/blacklists/custom/files 'attr(0755,squid,squid)' \
--dir /var/squidGuard/blacklists/custom 'attr(0755,squid,squid)' \
--dir /var/squidGuard/blacklists/cache.execlists 'attr(0755,ufdb,squid)'

echo "%doc COPYING" >> %{name}-%{version}-filelist
echo "%config /etc/squid/blacklists" >> %{name}-%{version}-filelist

%post

%preun

%files -f %{name}-%{version}-filelist
%defattr(-,root,root)
%dir %{_nseventsdir}/%{name}-update

%changelog
* Tue Nov 19 2019 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.9.2-1
- Web filter: invalid selected category - Bug NethServer/dev#5926

* Fri Jun 14 2019 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.9.1-1
- ufdbguard stops after configuration save - Bug NethServer/dev#5777

* Tue May 28 2019 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.9.0-1
- Web proxy Cockpit UI - NethServer/dev#5746

* Wed Jan 24 2018 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.8.0-1
- Content filter does not honor multiple timeframes - Bug NethServer/dev#5408
- UI: display a warning if proxy is disabled

* Thu Nov 16 2017 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.7.4-1
- Web filter: bad profile and filter validation - Bug NethServer/dev#5384

* Fri Oct 06 2017 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.7.3-1
- UI improvements: disable BlockIpAccess and BlockBuiltinRules by default

* Fri Jul 07 2017 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.7.2-1
- Web content filter: error when changing filter name - Bug NethServer/dev#5322

* Thu Jun 01 2017 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.7.1-1
- Web filter not working with profiles configured for a group (again) - Bug NethServer/dev#5307

* Tue Apr 04 2017 Davide Principi <davide.principi@nethesis.it> - 1.7.0-1
- Ufdbguard: Add checkbox to select all categories in the Filters tab - NethServer/dev#5257
- Make ufdbguard logs readable by the Server Manager - NethServer/dev#5255
- Web filter https default block page - NethServer/dev#5236

* Tue Jan 24 2017 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.6.2-1
- Web filter not working with profiles configured for a group - Bug NethServer/dev#5205

* Fri Dec 23 2016 Filippo Carletti <filippo.carletti@gmail.com> - 1.6.1-1
- Replace squidGuard with ufdbGuard - NethServer/dev#5171

* Fri Dec 16 2016 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.6.0-1
- Replace squidGuard with ufdbGuard - NethServer/dev#5171

* Thu Oct 06 2016 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.5.1-1
- Web filter: can't create profiles involving users - Bug NethServer/dev#5121

* Thu Jul 07 2016 Stefano Fancello <stefano.fancello@nethesis.it> - 1.5.0-1
- First NS7 release

* Mon Jun 06 2016 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.4.3-1
- Global whitelist not working when URLBlacklist.com is the Blacklists database - Bug #3398 [NethServer]

* Fri Mar 04 2016 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.4.2-1
- After deleting a filter into "Web content filter" you cannot manage the previously associate profile - Bug #3344 [NethServer]

* Fri Nov 20 2015 Davide Principi <davide.principi@nethesis.it> - 1.4.1-1
- Builtin filter rules for squidguard (field renamed) - Feature #3320 [NethServer]

* Fri Nov 20 2015 Davide Principi <davide.principi@nethesis.it> - 1.4.0-1
- Builtin filter rules for squidguard - Feature #3320 [NethServer]

* Tue Nov 10 2015 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.3.4-1
- Avoid squid restart after squidguard log rotation - Enhancement #3293 [NethServer]

* Mon Oct 12 2015 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.3.3-1
- New zones can't browse the net thorugh proxy - Bug #3275 [NethServer]

* Tue Sep 29 2015 Davide Principi <davide.principi@nethesis.it> - 1.3.2-1
- Make Italian language pack optional - Enhancement #3265 [NethServer]
- SquidGuard stops logging after logorate - Bug #3190 [NethServer]

* Mon Jun 22 2015 Davide Principi <davide.principi@nethesis.it> - 1.3.1-1
- SquidGuard stops logging after logorate - Bug #3190 [NethServer]

* Thu Apr 23 2015 Davide Principi <davide.principi@nethesis.it> - 1.3.0-1
- Support  IP ranges and CIDR subnets into Web Content Filter profiles - Enhancement #3119 [NethServer]

* Fri Apr 10 2015 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.2.0-1
- Permit zone and roles into Web Content Filter profiles - Feature #3084 [NethServer]

* Tue Jan 20 2015 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.1.0-1.ns6
- squidGuard: manage blacklist source - Enhancement #2959 [NethServer]
- squidGuard: support multiple profiles - Enhancement #2958 [NethServer]
- Enhance visual appearance of proxy block pages - Feature #2866 [NethServer]
- squidGuard: break 99acl template - Enhancement #2853 [NethServer]
- Content filter: add time table support - Feature #1984 [NethServer]

* Wed Oct 15 2014 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.0.5-1.ns6
- squidGuard.log not rotated - Bug #2869
- Fix "Permission denied" error on web interface - Bug #2756

* Fri Jun 06 2014 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.0.4-1.ns6
- Fix "Permission denied" error on web interface - Bug #2756

* Wed Oct 16 2013 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.0.3-1.ns6
- Fix whitelist behavior #2287

* Fri Jul 26 2013 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.0.2-1.ns6
- Add nethserver-httpd dependency #1960
- Allow empty BlockedFileTypes property from web UI #1960
- File blacklist template: do not match anything when BlockedFileTypes is empty #1960

* Tue Jul 16 2013 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.0.1-1.ns6
- Fix italian translation #1960

* Fri Jun 21 2013 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.0.0-1.ns6
- First release. Feature #1960 #1959



