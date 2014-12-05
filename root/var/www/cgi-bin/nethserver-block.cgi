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
my $cgi = new CGI;
my ($clientaddr,$clientname,$clientuser,$clientgroup,$targetgroup,$url,$virus,$source,$user);
my (@supported,$image,$redirect,$autoinaddr,$proxy,$proxymaster);
my $lang="en"; 
my (%msgconf,%title,%logo,%msg,%tab,%word);
my ($protocol,$address,$port,$path,$refererhost,$referer);
my %Babel = ();
my $rechts="";
my $links="";
my $dummy="";
sub getpreferedlang(@);
sub parsequery($);
sub status($);
sub redirect($);
sub content($);
sub expires($);
sub msg($$);
sub gethostnames($);
sub spliturl($);
sub showhtml($);
sub showimage($$$);
sub showinaddr($$$$$);

#
# CONFIGURABLE OPTIONS:
#
# (Currently: "en", "fr", "de", "es", "nl", "no")
@supported   = (
                "en (English), ",
                "it (Italiano)."
               );

#
# Modifiy the values below to reflect you environment
# The image you define with "$image" and redirect will be displayed if the unappropriate
# url is of the type: gif, jpg, jpeg, png, mp3, mpg, mpeg, avi or mov.
#
$image       = "/images/blocked.gif";					# RELATIVE TO DOCUMENT_ROOT
$redirect    = "http://admin.your-domain/images/blocked.gif";		# "" TO AVOID REDIRECTION
$proxy       = "proxy.your-domain";					# Your proxy server
$proxymaster = "operator\@your-domain";					# The email of your proxy adminstrator
$autoinaddr  = 2;			# 0|1|2;
					# 0 TO NOT REDIRECT
					# 1 TO AUTORESOLVE & REDIRECT IF UNIQUE
					# 2 TO AUTORESOLVE & REDIRECT TO FIRST NAME

# You may wish to enter your company link and logo to be displayed on the page
my $company = " ";
my $companylogo = " ";

my $squidguard = "http://www.squidguard.org";
my $squidguardlogo = "http://www.squidguard.org/Logos/squidGuard.gif";

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
  my $query       = shift;

  my $clientaddr  = "$Babel{Unknown}";
  my $clientname  = "$Babel{Unknown}";
  my $clientuser  = "$Babel{Unknown}";
  my $clientgroup = "$Babel{Unknown}";
  my $targetgroup = "$Babel{Unknown}";

  my $virus = CGI::escapeHTML($cgi->param('virus')) || CGI::escapeHTML($cgi->param('malware')) || '';
  my $source = CGI::escapeHTML($cgi->param('source')) || '';
  my $user = CGI::escapeHTML($cgi->param('user')) || '';

  my $url         = "$Babel{Unknown}";
  if (defined($query)) {
    while ($query =~ /^\&?([^\&=]+)=\"([^\"]*)\"(.*)/ || $query =~ /^\&?([^\&=]+)=([^\&=]*)(.*)/) {
      my $key = $1;
      my $value = $2;
      $value = "$Babel{Unknown}" unless(defined($value) && $value && $value ne "unknown");
      $query = $3;
      if ($key =~ /^(clientaddr|clientname|clientuser|clientgroup|targetgroup|url)$/) {
	     eval "\$$key = \$value";
      }
      if ($query =~ /^url=(.*)/) {
	     $url = $1;
	     last;
      }
    }
  }
  return($clientaddr,$clientname,$clientuser,$clientgroup,$targetgroup,$url,$virus,$source,$user);
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
# SEND OUT AN IMAGE:
#
sub showimage($$$) {
  my ($type,$file,$redirect) = @_;
  content("image/$type");
  expires(300);
  redirect($redirect) if($redirect);
  print "\n";
  open(GIF, "$ENV{\"DOCUMENT_ROOT\"}$file");
  print <GIF>;
  close(GIF)
}

#
# SHOW THE INADDR ALERNATIVES WITH OPTIONAL ATOREDIRECT:
#
sub showinaddr($$$$$) {
  my ($targetgroup,$protocol,$address,$port,$path) = @_;
  my $msgid = $targetgroup;
  my @names = gethostnames($address);
  if($autoinaddr == 2 && @names || $autoinaddr && @names==1) {
    status("301 Moved Permanently");
    redirect("$protocol://$names[0]$port$path");
  } elsif (@names>1) {
    status("300 Multiple Choices");
  } elsif (@names) {
    status("301 Moved Permanently");
  } else {
    status("404 Not Found");
  }
  if (@names) {
    print "Content-type: text/html\n\n";
    print "<!DOCTYPE html PUBLIC \"-//W3C//DTD  HTML 4.0 Transitional//EN\" \"http://www.w3.org/TR/REC-html40/loose.dtd\">\n";
    print "<html><head>\n";
    print "<title>$Babel{Title}</title>\n";
    print "</head>\n";
    print "<body bgcolor=#E6E6FA> \n";
    expires(0);
    $msgid = "in-addr" unless(defined($msgconf{$msgid}));
    if (defined($msgconf{$msgid})) {
      print "  <!-- showinaddr(\"$msgid\") -->\n";
      for (@{$msgconf{$msgid}}) {
	my @config = split(/:/);
	my $type = shift(@config);
	if ($type eq "msg") {
	  msg($config[0],$config[1]);
	} elsif ($type eq "tab") {
	  table(shift(@config),shift(@config),@config);
	} elsif ($type eq "alternatives") {
	  print "  <TABLE BORDER=0 ALIGN=CENTER>\n";
	  for (@names) {
	    print "   <TR>\n    <TH ALIGN=LEFT>\n     <FONT SIZE=+1>";
	    href("$protocol://$_$port$path");
	    print "\n     </FONT>\n    </TH>\n   </TR>\n";
	  }
	  print "  </TABLE>\n\n";
	  if (defined($ENV{"HTTP_REFERER"}) && $ENV{"HTTP_REFERER"} =~ /:\/\/([^\/:]+)/) {
	    $refererhost = $1;
	    $referer = $ENV{"HTTP_REFERER"};
	    msg("H4","referermaster");
	  }
	}
      }
    } 
  }
  return;
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

($clientaddr,$clientname,$clientuser,$clientgroup,$targetgroup,$url,$virus,$source,$user) = parsequery($ENV{"QUERY_STRING"});


($protocol,$address,$port,$path) = spliturl($url);

if ($url =~ /\.(gif|jpg|jpeg|png|mp3|mpg|mpeg|avi|mov)$/i) {
  status("403 Forbidden");
  showimage("gif",$image,$redirect);
  exit 0;
}
if ($targetgroup eq "in-addr") {
   showinaddr($targetgroup,$protocol,$address,$port,$path);
}

# template of SQUIDGUARD
if($virus eq '' || $source eq '') {

  status("403 Forbidden");
  expires(0);
  print "Content-type: text/html\n\n";
  print "<!DOCTYPE html PUBLIC \"-//W3C//DTD  HTML 4.0 Transitional//EN\" \"http://www.w3.org/TR/REC-html40/loose.dtd\">\n";
  print "<html><head>\n";
  print "<title>$Babel{Title}</title>\n";
  print "</head>\n";
  print "<body> \n";

  print qq{
  <style type="text/css">
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
  </style>  
    <div class="visu">
    <h2 style="text-align: center;">$Babel{Msg}</h2>
    <hr>
    <p>
  };

  print "\n";
  print "<a href=$company>\n";
  print "<img align=left border=0 alt=\"\" src=$companylogo></a>\n";

  print "$Babel{Tabclientgroup}&nbsp;<b>$clientgroup $targetgroup</b><br>\n";
  print "$Babel{Taburl}&nbsp;<b>$url</b><br>\n";

  if ($targetgroup eq "in-addr") {
     print "$Babel{msginaddr}<br><br>\n";
     print "$Babel{msgnoalternatives} <U>",$address,"</U>.<br>\n";
     print "$Babel{msgwebmaster}\n";
  }
  print "<br>\n";

  print "$Babel{Origin}: <b>$clientaddr</b>";
  if($clientuser != "") {
    print "$Babel{User}: <b>$clientuser</b>";
  }

  print qq{
    <p>
    <hr>
    <div style="float:right;">Powered by <a href="http://www.squidguard.org">SquidGuard</a></div>
  };

  print "</body></html>\n";
}

# template of SQUIDCLAMAV
else {
  # Remove unused infos
  $source =~ s/\/-//;
  $virus =~ s/stream: //;
  $virus =~ s/ FOUND//;

  # browsing unsafe
  if ($virus =~ /Safebrowsing/) {
    print $cgi->header();
    print $cgi->start_html(-title => "Babel{TitleBrowseUnsafe}", -bgcolor => "#353535");
    print qq{
    <style type="text/css">
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
    </style> 
      <div class="visu">
      <h2 style="text-align: center;">$Babel{TitleBrowseUnsafe}</h2>
      <hr>
      <p>
    };
    print qq{
      $Babel{ReqURL} <b>$url</b> $Babel{urlerrorUnsafe}<br>
      $Babel{subtitleUnsafe}: <b>$virus</b></p>
    };

    print qq{
      <p>
      $Babel{errorreturnUnsafe}
      <p>
      };

    print "$Babel{Origin}: <b>$source</b><br>";

    if($user eq '') {
      print " ";
    }
    else
    {
      print "$Babel{User}: <b>$user</b>";
    }

    print qq{
      <p>
      <hr>
      <div style="float:right;">Powered by <a href="http://squidclamav.darold.net/">SquidClamAv</a></div>
    };
  
  print $cgi->end_html();

  # virus detected
  } else {
    print $cgi->header();
    print $cgi->start_html(-title => "$Babel{TitleVirus}", -bgcolor => "#353535");
    print qq{
    <style type="text/css">
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
    </style> 
      <div class="visu">
      <h2 style="text-align: center;">$Babel{TitleVirus}</h2>
      <hr>
      <p>
    };

    print qq{
      $Babel{ReqURL} <b>$url</b> $Babel{urlerror}<br>
      $Babel{subtitle}: <b>$virus</b></p>
    };

    print qq{
      <p> 
      $Babel{errorreturnUnsafe}
      <p>
    };

    print "$Babel{Origin}: <b>$source</b><br>";

    if($user eq '') {
      print " ";
    }
    else
    {
      print "$Babel{User}: <b>$user</b>";
    }

    print qq{
      <p>
      <hr>
      <div style="float:right;">Powered by <a href="http://squidclamav.darold.net/">SquidClamAv</a></div>
    };

    print $cgi->end_html();
  }
}

exit 0 ;