Summary: NethServer squidGuard configuration
Name: nethserver-squidguard
Version: 1.1.0
Release: 1
License: GPL
URL: %{url_prefix}/%{name} 
Source0: %{name}-%{version}.tar.gz
BuildArch: noarch

Requires: nethserver-httpd, nethserver-squid >= 1.3.0
Requires: squidGuard

BuildRequires: perl
BuildRequires: nethserver-devtools 

%description
NethServer squidGuard configuration

%prep
%setup

%build
%{makedocs}
perl createlinks

%install
rm -rf $RPM_BUILD_ROOT
(cd root; find . -depth -print | cpio -dump $RPM_BUILD_ROOT)
%{genfilelist} $RPM_BUILD_ROOT > %{name}-%{version}-filelist  \
--dir /var/squidGuard/blacklists/custom/blacklist 'attr(0755,squid,squid)' \
--dir /var/squidGuard/blacklists/custom/whitelist 'attr(0755,squid,squid)' \
--dir /var/squidGuard/blacklists/custom/files 'attr(0755,squid,squid)' \
--dir /var/squidGuard/blacklists/custom 'attr(0755,squid,squid)'

echo "%doc COPYING" >> %{name}-%{version}-filelist
echo "%config /etc/squid/blacklists" >> %{name}-%{version}-filelist

%post

%preun

%files -f %{name}-%{version}-filelist
%defattr(-,root,root)

%changelog
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



