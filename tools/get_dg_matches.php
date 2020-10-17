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

// loads configuration
$cfg = [];
include "../config.php";
if (file_exists(  "../config.local.php")) {
    include  "../config.local.php";
}

$linkBaseDG = "http://www.dailygammon.com/bg/user/";
$linkExportDG = "http://www.dailygammon.com/bg/export/";
$mainWget = "wget --quiet --header=\"Cookie: USERID={$cfg["user"]}; PASSWORD={$cfg["pass"]}\"";

if ($cfg["user"] === "" || $cfg["pass"] === "") {
    echo "Please set \$cfg['user'] and \$cfg['pass'] in ../config.local.php or ../config.php \n";
    exit;
}


// read thisOutput directory to pretend double analyses
// ************************************************
// get directory handle
$dp = opendir($cfg["pathMat"]);

// read directory and build an array of values
$oldMatches = []; // will contain all existings matches
$newMatches = [];

// our later Output
$thisOutput[] = "";


$i = 0;
while (($fileExist = readdir($dp)) !== false) {
    if ($fileExist !== "." && $fileExist !== ".." && false !== strpos($fileExist, ".mat")) {
        $oldMatches[$i] = str_replace(".mat", "", $fileExist);
        $i++;
    }
}

// get page with finished matches of the last 180 days
$execute = $mainWget . " -O - " . $linkBaseDG . $cfg['user'] . "?finished=1&days_to_view=" . $cfg["daysToView"];

exec($execute, $finished);

// get match numbers
if (!empty($finished) && is_array($finished)) {
    foreach ($finished as $key => $val) {
        if (preg_match("/<A href=(.*?)>Review<\/A>/", $val)) {
            preg_match("/bg\/game\/(\d+)/", $val, $match);
            $newMatches[] = $match[1];
        }
    }
}

// the new matches
$newMatches = array_diff($newMatches, $oldMatches);

$cntNewMatches = count($newMatches);
$cntOldMatches = count($oldMatches);


if ($cntNewMatches || $cntOldMatches) {
    $thisOutput[] = "{$cntOldMatches} existing and {$cntNewMatches} new matches";
}

// get the new match filesShow
foreach ($newMatches as $key => $val) {
    $thisOutput[] = "Getting Game #{$val}";

    $execute = $mainWget . " -O " . $cfg["pathMat"] . $val . ".mat" . " " . $linkExportDG . $val;

    $status = exec($execute);

    if ($status > "") {
        $thisOutput[] = "Error getting Game #{$val} - Status: {$status}";
    }
}

$fullOutput = implode('<br>', $thisOutput);

$tplBody = <<< HTML
<!DOCTYPE html>
<html lang="en" dir="ltr" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <link rel="stylesheet" href="../styles.css" type="text/css"/>
    <title>Downloading *.mat files from dailygammon.com</title>
</head>
<body>
<h1>Downloading *.mat files from dailygammon.com</h1>
<p>{$fullOutput}</p>
<p>Open <a href='write_gnucmd.php'>write_gnucmd</a> to write the gnubg command file</p>
</body>
</html>
HTML;

echo $tplBody;