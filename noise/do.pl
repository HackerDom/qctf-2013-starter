#!/usr/bin/perl

use strict;
use warnings;

use constant {
	SAMPLING_RATE => 44100,
	SILENCE_LEVEL => 16384,
	MAX_AMPLITUDE => 32768,
	SEED => 0
};
	
use constant {DOT_SIZE => int(SAMPLING_RATE * 0.005)};

use Morse;
use WAV;

if ($#ARGV == -1) {
	print STDERR "usage: $0 <key file> <output file>";
	exit;
}

srand SEED;

sub noise {
	my $len = shift;
	my $ampl = shift || MAX_AMPLITUDE;

	my $res = '';

	$res .= pack "S*", (int rand (2 * $ampl) - $ampl) for 0 .. $len;

	return $res;
}

sub silence {
	return noise($_[0], SILENCE_LEVEL);
}

sub space {
	return silence(DOT_SIZE);
}

sub dot {
	return noise(DOT_SIZE).space();
}

sub dash {
	return noise(3 * DOT_SIZE).space();
}

my %sounds = (
	' ' => \&space,
	'.' => \&dot,
	'-' => \&dash,
);

$\ = undef;

open KEY, '<', $ARGV[0] or die "$!";
my @text = split //, <KEY>;
close KEY;

my $res = '';

for (@text) {
	my @sig = split //, Morse::encryptChar($_);

	push @sig, (' ', ' ');

	$res .= $sounds{$_}() for @sig;
}

open OUT, '>', $ARGV[1] or die "$!";
binmode OUT;

print OUT WAV::createHeader length($res), SAMPLING_RATE;
print OUT $res;

close OUT;
