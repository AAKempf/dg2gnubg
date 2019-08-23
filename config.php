<?php
/**
 * Configuration for DG match getter and the writer of the gnubg commands
 *
 * Your dailygammon setting is in tools/get_dg_matches.php
 *
 */

$cfg = [];

// Your name on Dailygammon. To hide it in the games list
$cfg["players_name"] = "";

// The players profile on dailygammon.
// If it's set, we set also a link with the players name on the start page
// $cfg["dg_profile"] = "http://www.dailygammon.com/bg/user/ID";
$cfg["dg_profile"] = "";

// general gnubg-settings for the match analysis
// to see the possible commands, open "gnubg -t" and type there "? set"  or use help in gnubg gui
$cfg["gnubg_ini_head"] = '
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
';

// Additional gnubg commands for each match
$cfg["gnubg_ini_list"] = [
    "import mat {path_mat}{file_mat}",  // import match
    "analyse match",  // analyze match
    "save match {path_sgf}{file_sgf}", // save as sgf file first, see https://savannah.gnu.org/bugs/index.php?50617
    "relational add match", // save statistics to player records in sql database
    "load match {path_sgf}{file_sgf}", // load the sgf file
    "export match html {path_html}{file_html}", // export it to the path $cfg["path_html"]
    // "export match pdf {path_pdf}{file_pdf}"  // export it to the path $cfg["path_pdf"]
];

// Replace one player name to another
// usefull for some Fibs-clients which writes "You" as your player name in the .mat files
// Example:
// $cfg["replace_name_from"] = ["name1","name2"]
// $cfg["replace_name_to"] = ["name1new","name2new"];
$cfg["replace_name_from"] = [];
$cfg["replace_name_to"] = [];

// contains player names which don't wanna see themself in the results
$cfg["ignore_players"] = [];

// contains game numbers to ignore them, maybe private ones
$cfg["ignore_games"] = ["1", "2", "3"];

// Path Names
// source path of match files
$cfg["path_mat"] = __DIR__ . "/matches/dailygammon/";

// export path for html files
$cfg["path_html"] = __DIR__ . "/matches/html/";

// export path for PDF files
$cfg["path_pdf"] = __DIR__ . "/matches/pdf/";

// export path for SGF files
$cfg["path_sgf"] = __DIR__ . "/matches/sgf/";

// link path to the analyzed matches
$cfg["link_games"] = "matches/html/";

// command file path and name for gnubg later on
// we fill it always with new commands
$cfg["path_gnubg_ini"] = __DIR__ . "/tools/gnubg-batch-export.ini";

// Linux path and file name to gnubg
$cfg["gnubg_cmd"] = "gnubg -t -q --lang=en_GB --datadir=\$HOME/.gnubg";   // linux

// Windows path and file name to gnubg (untested with datadir)
$cfg["gnubg_cmd_win"] = "gnubg-no-gui -t -q --lang=en_GB --datadir=%USERPROFILE%\.gnubg";

// pattern for exported match file name, usually no need to change it.
// browse.php needs this order
// -----------------------------------
// {fn} = name of the file without extension, f.e. "55226632.mat" gets "55226632"
// {mp} = match points, with leading zeros
// {p1} = player 1 name
// {p2} = player 2 name
$cfg["file_name_pattern"] = "{fn}_{mp}p_{p1}_{p2}";

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
}

/**
 * Checks the form fields values against a file name
 *
 * @param array $form_fields
 * @param string $check
 * @param string $val
 * @return string filename
 */
function GetFileByField(array $form_fields, string $check, string $val): string
{
    $found = '';

    if (array_key_exists("s", $form_fields) && $form_fields["s"] > "") {
        if (strpos($check, $form_fields["s"])) {
            $found = $val;
        }
    } elseif (array_key_exists("p", $form_fields) && $form_fields["p"] > "") {

        if (strpos($check, $form_fields["p"])) {
            $found = $val;
        }
    } else if (false !== strpos($check, $form_fields["p"]) && false !== strpos($check, $form_fields["s"])) {
        $found = $val;
    }

    return $found;

}