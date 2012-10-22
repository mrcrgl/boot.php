#!/usr/bin/perl

use strict;

foreach my $file (@ARGV) {
  next unless -f $file;
  print $file."\n";
  
  open FILE, $file;
  undef $/;
  my $lines = <FILE>;
  close FILE;
  
  #$lines =~ s/\r\n/\n/g;
  #$lines =~ s/\t/\ \ \ \ /g;
  #$lines =~ s/Versions\./boot\.php\./g;
  $lines =~ s/\ V([A-Z]{1})([a-z]{1})/\ B$1$2/g;
  $lines =~ s/VDebug/BDebug/g;
  $lines =~ s/VSettings/BSettings/g;
  $lines =~ s/VObject/BObject/g;
  $lines =~ s/versions\./boot\./g;

  #$lines =~ s/\)(\s+)\{/\)\ \{/g;
  #$lines =~ s/([\ ]+)(\W+)(.*)function(.*)\)(\s?)\{/$1$2$3function$4\)\n$1\{/g;
  #$lines =~ s/(\S+|\S?)(.*)class(.*)(\s?)\{/$1$2class$3\n\{/g;
  
#  $lines  =~ s/if\(/if\ \(/g;
#  $lines  =~ s/foreach\(/foreach\ \(/g;
#  $lines  =~ s/while\(/while\ \(/g;
#  $lines  =~ s/catch\(/catch\ \(/g;
#  $lines  =~ s/\}(\s+)else/\}\ else/g;

  #exit;
  #print $lines;


  #continue;
  open FILE, "> $file";
  print FILE $lines;
  close FILE;

}
