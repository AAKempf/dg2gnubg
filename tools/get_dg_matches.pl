#!/usr/bin/perl -w
use strict;

# Jon Nall
# nall AT themountaingoats DOT net
# 06.01.04
#
# Released under the GNU GPLv2 License


$main::GAME_DIR = "$ENV{HOME}/dg_matches";
$main::USER="";          # USERID from your cookies file
$main::PASS=""; # PASSWORD from your cookies file

$main::DG_BASE = "http://www.dailygammon.com/bg/user/";
$main::DG_REVIEW_BASE = "http://www.dailygammon.com/bg/export/";
$main::WGET = "/usr/bin/wget --header='Cookie: USERID=$main::USER; PASSWORD=$main::PASS'";


sub main()
{
    if ($main::USER eq "" || $main::PASS eq "")
    {
        printf(STDERR "Please set USER and PASS at the top of this script.\n");
        printf(STDERR "These values are most likely in your browser's cookies file\n");
        exit(1);
    }
    my $gameref = &getMatchNumbers();
    my @games = @{$gameref};

    mkdir($main::GAME_DIR);
    foreach my $id (@games)
    {
        printf("Retrieving Game# $id\n");
        system("$main::WGET -O '${main::GAME_DIR}/${id}.mat' '${main::DG_REVIEW_BASE}/$id' > /dev/null 2>&1");
    }
}


sub getMatchNumbers()
{
    my @games = ();

    my @results = `$main::WGET -O '-' '${main::DG_BASE}/${main::USER}?finished=1' 2> /dev/null`;

    foreach my $line (@results)
    {
       if($line =~ /<A href=(.*?)>Review<\/A>/) {
           my $game_url = $1;
           $game_url =~ /\/bg\/game\/([0-9]+)\//;
           push(@games, $1);
        }
    } 

    return \@games;
}

main::main();
