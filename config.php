<?php
/**
 * Configuration for DG match getter and the writer of the gnubg commands
 */

$cfg = [];

/**
 * The following vars could also be set in a file named
 * config.local.php to overwrite this ones
 */

// Your name on Dailygammon. To hide it in the games list, fill it here
$cfg["playersName"] = "";

// The players profile on dailygammon.
// If it's set, we set also a link with the players name on the start page
// $cfg["dgProfile"] = "http://www.dailygammon.com/bg/user/ID";
$cfg["dgProfile"] = "";

// USERID from your DG cookie
$cfg["user"] = "";

// PASSWORD from your DG cookie (will be encrypted)
$cfg["pass"] = "";

// contains player names which don't wanna see themself in the results
// $cfg["ignorePlayers"] = ['player1', 'player2'];
$cfg["ignorePlayers"] = [''];

// to limit the list of games on DG
$cfg["daysToView"] = 60;

// general gnubg-settings for the match analysis
// to see the possible commands, open "gnubg -t" and type there "? set"  or use help in gnubg gui
$cfg["iniHeadGnuBg"] = <<< TEXT
set threads 3 
set analysis moves on
set analysis window on
set analysis cubedecision evaluation plies 2
set analysis chequerplay evaluation plies 2
set analysis luckanalysis plies 1
set clockwise on
set export html pictureurl "html-images/"
set export html type "gnu"
set export html css external
set export html size 1
set export png size 1
set export moves display verybad yes
set export moves display bad yes
set export moves display doubtful yes
set export moves display unmarked no
set export cube display verybad yes
set export cube display bad yes
set export cube display doubtful yes
set export cube display unmarked no
set export cube display actual yes
set export cube display missed yes
set export cube display close yes
set record on
set sound enable no

TEXT;

// Additional gnubg commands for each match
$cfg["iniListGnuBg"] = [
    "import mat {pathMat}{file_mat}",  // import match
    "analyse match",  // analyze match
    "save match {pathSgf}{file_sgf}", // save as sgf file first, see https://savannah.gnu.org/bugs/index.php?50617
    "relational add match", // save statistics to player records in sql database
    "load match {pathSgf}{file_sgf}", // load the sgf file
    "export match html {pathHtml}{file_html}", // export it to the path $cfg["pathHtml"]
    // "export match pdf {pathPdf}{file_pdf}"  // export it to the path $cfg["pathPdf"]
];

// Replace one player name to another
// usefull for some Fibs-clients which writes "You" as your player name in the .mat filesShow
// Example:
// $cfg["replaceNameFrom"] = ["name1","name2"]
// $cfg["replaceNameTo"] = ["name1new","name2new"];
$cfg["replaceNameFrom"] = [];
$cfg["replaceNameTo"] = [];

// contains player names which don't wanna see themself in the results
// $cfg["ignorePlayers"] = ["name1","name2"];

// contains game numbers to ignore them, maybe private ones
$cfg["ignoreGames"] = ["1", "2", "3"];

// Path Names
// source path of match files
$cfg["pathMat"] = __DIR__ . "/matches/dailygammon/";

// export path for html files
$cfg["pathHtml"] = __DIR__ . "/matches/html/";

// export path for PDF files
$cfg["pathPdf"] = __DIR__ . "/matches/pdf/";

// export path for SGF files
$cfg["pathSgf"] = __DIR__ . "/matches/sgf/";

// link path to the analyzed matches
$cfg["linkGames"] = "matches/html/";

// GnuBg file path and name for gnubg later on
// we fill it always with new commands
$cfg["pathGnuBgIni"] = __DIR__ . "/tools/gnubg-batch-export.ini";

// Linux path and file name to gnubg
$cfg["cmdGnuBg"] = "gnubg -t -q --lang=en_GB --datadir=\$HOME/.gnubg";   // linux

// Windows path and file name to gnubg (untested with datadir)
$cfg["cmdGnuBgWin"] = "gnubg-no-gui -t -q --lang=en_GB --datadir=%USERPROFILE%\.gnubg";

// pattern for exported match file name, usually no need to change it.
// browse.php needs this order
// -----------------------------------
// {fn} = name of the file without extension, f.e. "55226632.mat" gets "55226632"
// {mp} = match points, with leading zeros
// {p1} = player 1 name
// {p2} = player 2 name
$cfg["patternFileName"] = "{fn}_{mp}p_{p1}_{p2}";

if (file_exists("./config.local.php")) {
    // overwrite:
    // $cfg["playersName"], $cfg["dgProfile"]
    // $cfg_user, $cfg_password
    include "./config.local.php";
}

// End of configuration
// ********************
// Well, would be nice to have a form for the stuff above...

/**
 * strpos on an array
 *
 * http://php.net/manual/en/function.strpos.php#102773
 *
 * @param $haystack
 * @param $needles
 *
 * @return bool|int
 */
function strpos_array($haystack, $needles)
{
    if (is_array($needles)) {
        foreach ($needles as $str) {
            if (is_array($str)) {
                $pos = strpos_array($haystack, $str);
            } else {
                $pos = strpos($haystack, $str);
            }
            if ($pos !== false) {
                return $pos;
            }
        }
    } else {
        return strpos($haystack, $needles);
    }
    return 0;
}

/**
 * Checks the form fields values against a file name
 *
 * @param array $formFields
 * @param string $check
 * @param string $val
 * @return string filename
 */
function getFileByField(array $formFields, string $check, string $val): string
{
    $found = '';

    if (array_key_exists("s", $formFields) && $formFields["s"] > "") {
        if (strpos($check, $formFields["s"])) {
            $found = $val;
        }
    } elseif (array_key_exists("p", $formFields) && $formFields["p"] > "") {

        if (strpos($check, $formFields["p"])) {
            $found = $val;
        }
    } else {
        if (false !== strpos($check, $formFields["p"]) && false !== strpos($check, $formFields["s"])) {
            $found = $val;
        }
    }

    return $found;
}