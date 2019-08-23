<?php
/**
 * Writes command file for gnubg from saved dailygammon matches
 *
 * If you wanna download matches from dailygammon: Use get_dg_matches.php first
 *
 * Author: Andreas Kempf aka 'amalesh' <a.github@amalesh.de>
 * Ver. 0.11
 * Licenced under GPL
 * ************************************************************
 * Abbr:
 * dg = dailygammon
 * fibs = first internet backgammon server
 * ************************************************************
 * This script is just experimental and for me one method
 * to learn gnubg's commands. Use it on your own risk :)
 */

// Configuration
// *************
include __DIR__ . "/../config.php";

// No need to change something below until you wanna change the script

// Start with the work
// *******************
// get directory handle
$dp = opendir($cfg["path_mat"]);

// read directory and build an array of values
// *******************************************

// contains all .mat file names later on
$match_numbers = [];

// contains all match infos later on
$match_infos = [];

$i = 0;
while (($file = readdir($dp)) !== false) {
    if ($file !== "." && $file !== ".."
        && false !== strpos($file, ".mat")
        && filesize($cfg["path_mat"] . $file) > 200
    ) {

        // file date
        $match_infos[$i]["fd"] = date("Y-m-d", fileatime($cfg["path_mat"] . $file));

        // {fn}
        $match_numbers[$i] = str_replace(".mat", "", $file);

        // file handle
        $fp = fopen($cfg["path_mat"] . $file, "rb");

        $firstname = "";

        $k = 0;
        while ($k < 4 && !feof($fp)) {
            /*           1    1    2    2    3    3    4
               0----5----0----5----0----5----0----5----0----x----x
             0|5 point match
             1|
             2|Game 1
             3|victory1 : 0                        amalesh : 0
            */
            $line = trim(fgets($fp, 72));

            switch ($k) {
                case "0": // {mp}
                    $match_infos[$i]["mp"] = trim(substr($line, 0, 2));

                    if (strlen($match_infos[$i]["mp"]) === 1) {
                        $match_infos[$i]["mp"] = "0" . $match_infos[$i]["mp"];
                    }
                    break;

                case "3": // {p1} {p2}
                    // Get the first name and reduce line to second name

                    // get the firstname
                    $line_len = strlen($line);
                    /** @noinspection ForeachInvariantsInspection */
                    for ($x = 0; $x < $line_len; $x++) {
                        $sTmp = $line[$x];
                        if ($sTmp !== ":") {
                            $firstname .= $sTmp;
                        } else {
                            $x = $line_len;
                        }
                    }
                    $line = str_replace([" : 0", $firstname], "", $line);

                    // replace double empty spaces
                    $line = preg_replace('/\s+/', ' ', $line);

                    $match_infos[$i]["p1"] = trim($firstname);
                    $match_infos[$i]["p2"] = trim($line);

                    if (count($cfg["replace_name_from"])) {
                        if (in_array($match_infos[$i]["p1"], $cfg["replace_name_from"], true)) {
                            $match_infos[$i]["p1"] = str_replace($cfg["replace_name_from"], $cfg["replace_name_to"],
                                $match_infos[$i]["p1"]);
                        }
                        if (in_array($match_infos[$i]["p2"], $cfg["replace_name_from"], true)) {
                            $match_infos[$i]["p2"] = str_replace($cfg["replace_name_from"], $cfg["replace_name_to"],
                                $match_infos[$i]["p2"]);
                        }
                    }

                    break;

                default:

                    break;
            }
            $k++;
        }
        fclose($fp);

        // check for names to ignore / skip
        if (isset($cfg["ignore_players"]) && in_array($firstname, $cfg["ignore_players"], true)) {
            unset($match_infos[$i]);
        }
        // check for games to ignore / skip
        if (isset($cfg["ignore_games"]) && in_array($match_infos[$i]["mp"], $cfg["ignore_games"], true)) {
            unset($match_infos[$i]);
        }

        if (isset($match_infos[$i])) {
            $i++;
        }
    }
}
// close directory
closedir($dp);

// read output directory to pretend double analyses
$dp = opendir($cfg["path_html"]);

// analyzed matches
$analyze_exists = [];

while (($file_analyze = readdir($dp)) !== false) {
    if ($file_analyze !== "." && $file_analyze !== ".." && false !== strpos($file_analyze, ".html")) {
        // check if match number (filename) is in older output file name
        foreach ($match_numbers as $key => $val) {
            // matches
            if ($val && false !== strpos($file_analyze, $val)) {
                $analyze_exists[$key] = $val;
            }
        }

        reset($match_numbers);
    }
}
// html file already exists
if (count($analyze_exists)) {
    // remove double values
    $analyze_exists = array_unique($analyze_exists);

    // get only new files whit no output so far
    $analyze_new = array_diff($match_numbers, $analyze_exists);
} else {
    $analyze_new = $match_numbers;
}

// build the gnubg commands
// ************************
$command = $cfg["gnubg_cmd"];
if (DIRECTORY_SEPARATOR === "\\") {
   $command = $cfg["gnubg_cmd_win"];
}

$cmd_line = "# commands for {$command}" . $cfg["gnubg_ini_head"];

foreach ($analyze_new as $key => $val) {

    // Export file name
    // $cfg["file_name_pattern"] = "{fn}_{mp}p_{p1}_{p2}";

    // replace spaces in player name
    $player_1 = str_replace(" ", "-", $match_infos[$key]["p1"]);
    $player_2 = str_replace(" ", "-", $match_infos[$key]["p2"]);

    $change = [
        "{mp}" => $match_infos[$key]["mp"],
        "{fn}" => $analyze_new[$key],
        "{fd}" => $match_infos[$key]["fd"],
        "{p1}" => $player_1,
        "{p2}" => $player_2,
    ];

    // without extensions, will be added later
    $output_file = strtr($cfg["file_name_pattern"], $change);

    $cmd_line .= "# "
        . $match_infos[$key]["mp"]
        . " point match: "
        . $match_infos[$key]["p1"]
        . " vs. "
        . $match_infos[$key]["p2"]
        . "\n";

    $change = [
        "{path_mat}" => $cfg["path_mat"],
        "{file_mat}" => $analyze_new[$key] . ".mat",
        "{path_html}" => $cfg["path_html"],
        "{file_html}" => $output_file . ".html",
        "{path_sgf}" => $cfg["path_sgf"],
        "{file_sgf}" => $output_file . ".sgf",
        "{path_pdf}" => $cfg["path_pdf"],
        "{file_pdf}" => $output_file . ".pdf",
    ];

    if (isset($cfg["gnubg_ini_list"])) {
        foreach ($cfg["gnubg_ini_list"] as $key2 => $val2) {

            if (false !== strpos($val2, "{")) {
                $val2 = strtr($val2, $change);
            }
            $cmd_line .= $val2 . PHP_EOL;
        }
    }
}

// for use from shell, see workaround.sh
if (!empty($argv[1])) {
    parse_str($argv[1], $_GET);
}

// write the command file
// **********************
$cmd_written = false;
if (count($analyze_new)) {
    $fp = fopen($cfg["path_gnubg_ini"], "wb");
    $cmd_written = fwrite($fp, $cmd_line);
    fclose($fp);
}

// write_gnucmd.php?write=1&cli=1
// to write the command file only and suppress this output
if(!isset($_GET["cli"])) {

?><!DOCTYPE html>
<html lang="en" dir="ltr" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <link rel="stylesheet" href="../styles.css" type="text/css"/>
    <title>Writes command file for GnuBG from saved backgammon matches</title>
</head>

<body>
<h1>Writes command file for GnuBG from saved backgammon matches</h1>

<p>You have <?= count($analyze_exists) ?> analyzed and <?= count($analyze_new) ?> non-analyzed matches</p>

<?php

if ($cmd_written) { ?>
    <h3>GnuBGcommand file succesfully written</h3>
    <p>Open your terminal and copy & paste the following line to it:</p>
    <pre class="ini"><?= $command . " -c " . $cfg["path_gnubg_ini"] ?></pre>
    <p>The content of the ini-file:</p>
    <pre class="ini"><?= $cmd_line ?></pre>
<?php } else { ?>
    <h3>Error writing GnuBG command file, maybe all .mat-files have been analyzed</h3>
<?php } ?>

</body>
</html>
<?php }