#!/bin/sh
# needed because of a bug in gnubg 1.05 on ubuntu 16.04
#
# after around 7-15 analyzed matches it stops with
# *** stack smashing detected ***: gnubg terminated
#
# see https://savannah.gnu.org/bugs/index.php?50617
#
# so we run it in a loop which also generates the new commands

counter=3
while [ $counter -gt 0 ]
do
    echo Start $(date)
    php -q write_gnucmd.php "write=1&cli=1"
    sleep 30s
    gnubg -t -q --lang=en --datadir=$HOME/.gnubg -c ./../gnubg-batch-export.ini
    ((counter--))
done
echo Stop $(date) done
