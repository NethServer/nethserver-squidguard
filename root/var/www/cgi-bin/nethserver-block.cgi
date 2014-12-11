#! /usr/bin/perl 
#
# Explain to the user that the URL is blocked and by which rule set
#
# Original by Pål Baltzersen 1999 (pal.baltzersen@ost.eltele.no)
# French texts thanks to Fabrice Prigent (fabrice.prigent@univ-tlse1.fr)
# Dutch texts thanks to Anneke Sicherer-Roetman (sicherer@sichemsoft.nl)
# German texts thanks to Buergernetz Pfaffenhofen (http://www.bn-paf.de/filter/)
# Spanish texts thanks to Samuel GarcÃ­a).
# Rewrite by Christine Kronberg, 2008, to enable an easier integration of
# other languages.
#

# By accepting this notice, you agree to be bound by the following
# agreements:
# 
# This software product, squidGuard, is copyrighted (C) 1998-2008
# by Christine Kronberg, Shalla Secure Services. All rights reserved.
# 
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License (version 2) as
# published by the Free Software Foundation.  It is distributed in the
# hope that it will be useful, but WITHOUT ANY WARRANTY; without even
# the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
# PURPOSE.  See the GNU General Public License (GPL) for more details.
# 
# You should have received a copy of the GNU General Public License
# (GPL) along with this program.

use strict;
use Socket;
use CGI;
#
# GLOBAL VALUES:
#
my ($clientaddr,$clientgroup,$targetgroup,$url,$virus,$source,$user);
my (@supported,$redirect);
my $lang="en"; 
my (%msgconf,%msg);
my ($protocol,$address,$port,$path);
my %Babel = ();
my $rechts="";
my $links="";
my $style_css = '<style type="text/css">
                  .visu {
                    border:1px solid #C0C0C0;
                    color:#FFFFFF;
                    position: relative;
                    min-width: 13em;
                    max-width: 52em;
                    margin: 4em auto;
                    border-radius: 10px;
                    padding: 3em;
                    -moz-padding-start: 30px;
                    background-color: #8b0000;
                  }
                  .visu h2, .visu h3, .visu h4 .visu body {
                    font-size:130%;
                    font-family: sans-serif;
                    font-style:normal;
                    font-weight:bolder;
                  }
                  body {
                    background-color: #353535;
                    font-family: sans-serif;
                  }
                  a:link {
                    color: #ffffff;
                  }
                  a:visited {
                    color: #ffff00;
                  }
                </style>';

sub getpreferedlang(@);
sub parsequery($);
sub status($);
sub redirect($);
sub content($);
sub expires($);
sub msg($$);
sub gethostnames($);
sub spliturl($);
sub printHTML($$$$);

#
# CONFIGURABLE OPTIONS:
#
# (Currently: "en", "fr", "de", "es", "nl", "no")
@supported   = (
                "en (English), ",
                "it (Italiano)."
               );
########################################################################################
#
# SUBROUTINES:
#

#
# RETURN THE FIRST SUPPORTED LANGUAGE OF THE BROWSERS PREFERRED OR THE
# DEFAULT:
#
sub getpreferedlang(@) {
  my @supported = @_;
  my @languages = split(/\s*,\s*/,$ENV{"HTTP_ACCEPT_LANGUAGE"}) if(defined($ENV{"HTTP_ACCEPT_LANGUAGE"}));
  my $lang;
  my $supp;
  push(@languages,$supported[0]);
  for $lang (@languages) {
    $lang =~ s/\s.*//;
    $lang = substr($lang,0,2);
    for $supp (@supported) {
      $supp =~ s/\s.*//;
      return($lang) if ($lang eq $supp);
    }
  }
}

#
# PARSE THE QUERY_STRING FOR KNOWN KEYS:
#
sub parsequery($) {

  my $cgi = new CGI;
  my $query       = shift;

  # squidguard info
  my $clientaddr  = CGI::escapeHTML($cgi->param('clientaddr')) || '';
  my $clientgroup = CGI::escapeHTML($cgi->param('clientgroup')) || '';
  my $targetgroup = CGI::escapeHTML($cgi->param('targetgroup')) || '';

  # squidclamav info
  my $virus       = CGI::escapeHTML($cgi->param('virus')) || CGI::escapeHTML($cgi->param('malware')) || '';
  my $source      = CGI::escapeHTML($cgi->param('source')) || '';
  my $user        = CGI::escapeHTML($cgi->param('user')) || '';

  my $url         = CGI::escapeHTML($cgi->param('url')) || '';

  return($clientaddr,$clientgroup,$targetgroup,$url,$virus,$source,$user);
}

#
# PRINT HTTP STATUS HEARER:
#
sub status($) {
  my $status = shift;
  print "Status: $status\n";
}

#
# PRINT HTTP LOCATION HEARER:
#
sub redirect($) {
  my $location = shift;
  print "Location: $location\n";
}

#
# PRINT HTTP CONTENT-TYPE HEARER:
#
sub content($) {
  my $contenttype = shift;
  print "Content-Type: $contenttype\n";
}

#
# PRINT HTTP LAST-MODIFIED AND EXPIRES HEARER:
#
sub expires($) {
  my $ttl = shift;
  my $time = time;
  my @day = ("Sun","Mon","Tue","Wed","Thu","Fri","Sat");
  my @month = ("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");
  my ($sec,$min,$hour,$mday,$mon,$year,$wday) = gmtime($time);
  printf "Last-Modified: %s, %d %s %d", $day[$wday],$mday,$month[$mon],$year+1900;
  printf " %02d:%02d:%02d GMT\n", $hour,$min,$sec;
  ($sec,$min,$hour,$mday,$mon,$year,$wday) = gmtime($time+$ttl);
  printf "Expires: %s, %d %s %d", $day[$wday],$mday,$month[$mon],$year+1900;
  printf " %02d:%02d:%02d GMT\n", $hour,$min,$sec;
}

#
# REVERSE LOOKUP AND RETURN NAMES:
#
sub gethostnames($) {
  my $address = shift;
  my ($name,$aliases) = gethostbyaddr(inet_aton($address), AF_INET);
  my @names;
  if (defined($name)) {
    push(@names,$name);
    if (defined($aliases) && $aliases) {
      for(split(/\s+/,$aliases)) {
  next unless(/\./);
  push(@names,$_);
      }
    }
  }
  return(@names);
}

#
# SPLIT AN URL INTO PROTOCOL, ADDRESS, PORT AND PATH:
#
sub spliturl($) {
  my $url      = shift;
  my $protocol = "";
  my $address  = "";
  my $port     = "";
  my $path     = "";
  $url =~ /^([^\/:]+):\/\/([^\/:]+)(:\d*)?(.*)/;
  $protocol = $1 if(defined($1));
  $address  = $2 if(defined($2));
  $port     = $3 if(defined($3));
  $path     = $4 if(defined($4));
  return($protocol,$address,$port,$path);
}

#
# PRINT THE HTML TEMPLATE USING DIFFERENT MSGS
#
sub printHTML($$$$) {

  # legenda
  # @_[0] => Title
  # @_[1] => Subtitle
  # @_[2] => ErrorMessage
  # @_[3] => UrlErrorMessage


  print "Content-type: text/html\n\n<!DOCTYPE html PUBLIC \"-//W3C//DTD  HTML 4.0 Transitional//EN\" \"http://www.w3.org/TR/REC-html40/loose.dtd\">\n<html><head>\n";
  print "<title>$Babel{Title}</title>\n";
  print "</head>\n<body> \n";
  
  print $style_css;  

  print qq{
            <div class="visu">
            <h2 style="text-align: center;">$Babel{@_[0]}</h2>
            <hr>

            $Babel{ReqURL} <b>$url</b> $Babel{@_[3]}<br>
            $Babel{@_[1]}: <b>$virus</b></p>

            <p>
            $Babel{@_[2]}
            <p>
          };

  print "$Babel{Origin}: <b>$source</b><br>";

  if($user eq '') {
    print " ";
  } else {
    print "$Babel{User}: <b>$user</b>";
  }

  print '<hr><div style="float:right;">Powered by <a href="http://squidclamav.darold.net/">SquidClamAv</a></div></body></html>';
}


########################################################################################
#
#                                   MAIN   PROGRAM
#
# To change the messages in the blocked page please refer to the corresponding babel file.
#
$lang = getpreferedlang(@supported);

open (BABEL, "ns-babel.$lang") || warn "Unable to open language file:   $!\n";
flock (BABEL, 2);
   while (<BABEL>) {
      chomp $_ ;
      ($links, $rechts) =  split (/=/, $_);
       $Babel{$links} = $rechts;
    }
flock (BABEL, 8);
close (BABEL);

($clientaddr,$clientgroup,$targetgroup,$url,$virus,$source,$user) = parsequery($ENV{"QUERY_STRING"});

($protocol,$address,$port,$path) = spliturl($url);

# template of SQUIDGUARD
if($virus eq '' || $source eq '') {

  status("403 Forbidden");
  expires(0);

  print "Content-type: text/html\n\n<!DOCTYPE html PUBLIC \"-//W3C//DTD  HTML 4.0 Transitional//EN\" \"http://www.w3.org/TR/REC-html40/loose.dtd\">\n<html><head>\n";
  print "<title>$Babel{Title}</title>\n";
  print "</head>\n<body> \n";

  print $style_css;  

  print qq { 
            <div class="visu">
            <h2 style="text-align: center;">$Babel{Msg}</h2>
            <hr>
           };

  print "\n";

  print "$Babel{Tabclientgroup}&nbsp;<b>$clientgroup $targetgroup</b><br>\n";
  print "$Babel{Taburl}&nbsp;<b>$url</b><br>\n";

  if ($targetgroup eq "in-addr") {
    print "$Babel{msginaddr}<br><br>\n";
    
    # get hosts alternatives
    my @hostsalternatives = gethostnames($address);

    # check if there are hosts's names alternatives
    if(defined @hostsalternatives[0]) {
      print "$Babel{msgalternatives} <U>",@hostsalternatives[0],"</U>.<br>\n";
    } else {
      print "$Babel{msgnoalternatives}<U>",$address,"</U>.<br>\n";
    }

    print "$Babel{msgwebmaster}\n";
  }

  print "<br>\n";

  print "$Babel{Origin}: <b>$clientaddr</b>";

  print '<hr><div style="float:right;">Powered by <a href="http://www.squidguard.org">SquidGuard</a></div></body></html>';

}

# template of SQUIDCLAMAV
else {

  # Remove unused infos
  $source =~ s/\/-//;
  $virus =~ s/stream: //;
  $virus =~ s/ FOUND//;

  # browsing unsafe
  if ($virus =~ /Safebrowsing/) {
    # print HTML template with safe browsing params
    printHTML("TitleBrowseUnsafe","subtitleUnsafe","errorreturnUnsafe","urlerrorUnsafe");

  } else { # virus detected
    # print HTML template with virus or malware params
    printHTML("TitleVirus","subtitle","errorreturn","urlerror");
  }
}

exit 0 ;