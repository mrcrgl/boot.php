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
  $lines =~ s/VLIB/BLIB/g;
  $lines =~ s/VFRAMEWORK/BFRAMEWORK/g;
  $lines =~ s/VCORE/BCORE/g;
  $lines =~ s/VCONFIG/BCONFIG/g;
  $lines =~ s/VCOMPONENTS/BCOMPONENTS/g;
  $lines =~ s/VMODULES/BMODULES/g;
  $lines =~ s/VMODELS/BMODELS/g;
  $lines =~ s/VPLUGINS/BPLUGINS/g;
  $lines =~ s/VMIDDLEWARES/BMIDDLEWARES/g;
  $lines =~ s/VTEMPLATES/BTEMPLATES/g;

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
