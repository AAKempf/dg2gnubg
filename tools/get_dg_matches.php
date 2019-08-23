<?php
/**
 * Get's all the matches from dailygammon.com
 *
 * Basic idea from
 * Jon Nall - nall@themountaingoats.net - 06.01.04
 *
 * @see file get_dg_matches.pl
 *
 * A. Kempf - a.github@amalesh.de
 * Translation to PHP and all the other stuff, see README.md
 *
 * Released under the GNU GPLv2 License
 *
 */

// your dailygammon setting
$cfg_user = ""; # USERID from your DG cookie
$cfg_pass = ""; # PASSWORD from your DG cookie (will be encrypted)
$cfg_days_to_view = 60; // to limit the list of games on DG

// End of user configuration

$dg_base = "http://www.dailygammon.com/bg/user/";
$dg_export = "http://www.dailygammon.com/bg/export/";
$main_wget = "wget --quiet --header=\"Cookie: USERID={$cfg_user}; PASSWORD={$cfg_pass}\"";

if ($cfg_user === "" || $cfg_pass === "") {
    echo "Please set \$cfg_user and \$cfg_pass in this file \n";
    exit;
}

// loads configuration
include __DIR__ . '/../config.php';


// read output directory to pretend double analyses
// ************************************************
// get directory handle
$dp = opendir($cfg["path_mat"]);

// read directory and build an array of values
$exist_matches = []; // will contain all existings matches
$new_matches = [];

// our later output
$output[] = "";

$i = 0;
while (($file_exist = readdir($dp)) !== false) {
    if ($file_exist !== "." && $file_exist !== ".." && false !== strpos($file_exist, ".mat")) {
        $exist_matches[$i] = str_replace(".mat", "", $file_exist);
        $i++;
    }
}

// get page with finished matches of the last 180 days
$execute = $main_wget . " -O - " . $dg_base . $cfg_user . "?finished=1&days_to_view=" . $cfg_days_to_view;

exec($execute, $finished);

// get match numbers

if (!empty($finished) && is_array($finished)) {
    foreach ($finished as $key => $val) {
        if (preg_match("/<A href=(.*?)>Review<\/A>/", $val)) {
            preg_match("/bg\/game\/(\d+)/", $val, $match);
            $new_matches[] = $match[1];
        }
    }
}
// the new matches
$new_matches = array_diff($new_matches, $exist_matches);

$new_matches_count = count($new_matches);
$exist_matches_count = count($exist_matches);


if ($new_matches_count || $exist_matches_count) {
    $output[] = "{$exist_matches_count} existing and {$new_matches_count} new matches";
}

// get the new match files
foreach ($new_matches as $key => $val) {
    $output[] = "Getting Game #{$val}";

    $execute = $main_wget . " -O " . $cfg["path_mat"] . $val . ".mat" . " " . $dg_export . $val;

    $status = exec($execute);

    if ($status !== 0) {
        $output[] = "Error getting Game #{$val}";
        break;
    }
}


?><!DOCTYPE html>
<html lang="en" dir="ltr" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <link rel="stylesheet" href="../styles.css" type="text/css"/>
    <title>Downloading *.mat files from dailygammon.com</title>
</head>
<body>
<h1>Downloading *.mat files from dailygammon.com</h1>
<p><?= implode('<br>', $output) ?></p>
<p>Open <a href='write_gnucmd.php'>write_gnucmd</a> to write the gnubg command file</p>
</body>
</html>