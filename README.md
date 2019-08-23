Dailygammon/GNU Backgammon Tools Ver. 1.0
=
Download your own backgammon matches from dailygammon.com (DG) in a readable format for GNU Backgammon which can analyze and export them into different formats, e.g. HTML and PDF.
  
This small PHP collection "do the job" and give you also a simple site to browse the matches. 

You see it running at https://dg2gnubg.amalesh.de/

# Requirements
1. Apache/PHP
2. wget
3. GNU Backgammon

# Installation and Configuration
Save the files anywhere on your (local) web server.

Open tools/get_dg_matches.php and insert your User-ID and your Cookie to download the files from dailygammon.com.

Open config.php to edit more settings. Usually only your DG player name is needed.

Everything is explained in the files itself.

# Start
Open /tools/index.php in your browser. There are two links, use the first one to get the files from dailygammon.

On success you see a link for the writer of the gnu commands.
  
After GNU Backgammon exported the first files it's possible to open /index.php


## Directories
Contains the files to browse the matches. The directories can be set in the config file.

1. /matches/dailygammon - the match files from dailygammon
2. /matches/html - the exported matches in HTML
3. /matches/sgf - the exported matches in SGF
3. /matches/pdf - the exported matches in PDF

/tools - for downloading matches and writing the command file for GNU Backgammon


## Licence
GNU GPLv2 