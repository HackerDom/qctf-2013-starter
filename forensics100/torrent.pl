#!/usr/bin/perl

use strict;
use warnings;

use Bencode qw(bencode);
use Digest::SHA1 qw(sha1);

if ($#ARGV < 2) {
	print STDERR "usage: $0 <output file> <piece length> <files...>";
	exit;
}

my $out = shift @ARGV;
my $piecesLength = shift @ARGV;

my @files;
my $contents = "";

$/ = undef;
my $file;

for $file (@ARGV) {
	open IN, '<', $file or next;
	binmode IN;
	my $cont = <IN>;
	$contents .= $cont;
	push @files, {length => length $cont, path => [split /\//, $file]};
	close IN;
}

my %info = (
	"piece length" => $piecesLength,
	"name" => "task",
	"files" => \@files,
	"pieces" => ""
);

for (unpack "(a$piecesLength)*", $contents) {
	$info{"pieces"} .= sha1 $_;
}

open OUT, '>', $out or die "out: $!";

binmode OUT;
print OUT bencode({"info" => \%info, "announce" => "http://retracker.local"});

close OUT;
