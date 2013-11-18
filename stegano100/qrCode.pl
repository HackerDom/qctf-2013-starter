#!/usr/bin/perl

use warnings;
use strict;

use Imager::QRCode;

my %options = (
	size          => 8,
	margin        => 1,
	version       => 1,
	level         => 'L',
	casesensitive => 1,
	lightcolor    => Imager::Color->new(255, 255, 255),
);

my $qrcode_generator = Imager::QRCode->new(
		%options,
		darkcolor     => Imager::Color->new(0, 0, 0),
#		darkcolor     => Imager::Color->new(255, 255, 255),
	);

if ($#ARGV < 1) {
	print "usage: $0 <from> <to>\n";
	exit;
}

open IN, '<', $ARGV[0] or die $!;

undef $/;
$_ = <IN>;

my $img = $qrcode_generator -> plot($_);
$img -> write(file => 'tmp'.$ARGV[1]) or die $img -> errstr;

close IN;

open IN, '<', 'tmp'.$ARGV[1] or die $!;
binmode IN;
$_ = <IN>;
s/\0\0\0(?=\xff\xff\xff)/\xff\xff\xff/;

open OUT, '>', $ARGV[1] or die $!;
print OUT;
close OUT;
