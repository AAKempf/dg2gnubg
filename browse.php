<?php

// needed only here to save file names
session_start();


$cfg = [];
include __DIR__ . "/config.php";

// The Templates for the thisOutput

$tplBody = <<< HTML
<!DOCTYPE html>
<html lang="en" dir="ltr" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>Browse</title>
    <link rel="stylesheet" href="styles.css" type="text/css"/>
</head>
<body class="fontfamily">
{fullOutput}
</body>
</html>
HTML;

$tplTitle = <<< HTML
<strong>{player_info}, {cntMatches} {match_plural}, {cntGames} games</strong>
<hr>
HTML;

// template for the link // ,&#10;analyzed on {fileDate}
$tplLink = <<< HTML
<a href="{cfg_link_games}{val}" title="Match #{line_0}" target="games">{line_0}</a> 
<a href="browse.php?p={line_1}" title="Matches with {line_1}" class="ml6">{line_1}</a> 
<a href="browse.php?s={opponent}" title="{cntGames} {cntPlayerMatches} with {opponent}" class="ml6">{opponent}</a><br />
HTML;


$formFields = $_POST;
if (!$formFields) {
    $formFields = $_GET;
}
// the match filesShow we will show
$filesShow = [];
// the match filesShow to hide
$filesHidden = [];

if (isset($_SESSION["filesShow"])) {
    $filesShow = $_SESSION["filesShow"];
    $filesHidden = $_SESSION["filesHidden"];
} else {
    //get directory handle
    $dir = opendir($cfg["pathHtml"]);

    // read the analyzed file names
    while (($file = readdir($dir)) !== false) {
        if ($file !== "." && $file !== ".." && false !== strpos($file, ".html") && !strpos_array($file,
                $cfg["ignorePlayers"])) {

            if ($file[strlen($file) - 9] !== "_") {
                // trick to add a leading zero for 1-9pointer
                if ($file[6] === "_") {
                    $file = "0" . $file;
                }
                $filesShow[] = $file;
            } // end of file like "_002.html"
            else {
                $filesHidden[] = $file;
            }
        }
    }
    //close directory
    closedir($dir);

    rsort($filesShow);
    reset($filesShow);
}
if (!isset($_SESSION["filesShow"]) && count($filesShow)) {
    $_SESSION["filesShow"] = $filesShow;
}
if (!isset($_SESSION["filesHidden"]) && count($filesHidden)) {
    $_SESSION["filesHidden"] = $filesHidden;
}

// search stuff
if ($formFields) {
    if (array_key_exists("p", $formFields)) {
        $formFields["p"] = strtolower($formFields["p"]); // points
        $formFields["p"] = preg_replace('/[^0-9p]/', "", $formFields["p"]);
    }

    if (array_key_exists("s", $formFields)) {
        $formFields["s"] = strtolower($formFields["s"]); // player names
        $formFields["s"] = str_replace(" ", "-", $formFields["s"]);
        $formFields["s"] = preg_replace('/[^0-9A-z\-]/', "", $formFields["s"]);
    }
}

// The file names in array depending on the search
$linksShow = [];
if ($formFields["s"] || $formFields["p"]) {
    foreach ($filesShow as $key => $val) {

        if ($found = getFileByField($formFields, strtolower($val), $val)) {
            $linksShow[] = $found;
        }
    }
} else {
    $linksShow = $filesShow;
}

// get amount of matches with opponents
$playersShow = [];
foreach ($linksShow as $key => $val) {

    // delete leading zero
    $val = trim($val, '0');

    $change = [
        '.html' => "",
        '-' => " ",
        $cfg["playersName"] => "", // removes the name
    ];

    $thisLine = explode("_", strtr($val, $change));
    // the opponent playersShow name
    $playerName = $thisLine[2] ?: $thisLine[3];

    $playersShow[$playerName]++;
}

// Just to count later on the hidden filesShow per search
$hiddenFiles = [];

if (count($filesHidden)) {

    if ($formFields["p"] || $formFields["s"]) {
        foreach ($filesHidden as $key => $val) {
            if ($found = getFileByField($formFields, strtolower($val), $val)) {
                $hiddenFiles[] = $found;
            }
        }
    } else {
        $hiddenFiles = $filesHidden;
    }
}

$cntGames = count($hiddenFiles) + count($linksShow);
$cntMatches = count($linksShow);
$cntPlayers = count($playersShow);

$thisOutput[] = ""; // thisOutput


if ($cntMatches) {

    $change = [
        '{cntGames}' => $cntGames,
        '{cntMatches}' => $cntMatches,
        '{match_plural}' => $cntMatches === 1 ? 'match' : 'matches',
        '{player_info}' => $cntPlayers > 1 ? $cntPlayers . ' players' : '',
    ];

    $thisOutput[] = strtr($tplTitle, $change);
}


if (is_array($linksShow)) {

    $i = 0;
    foreach ($linksShow as $key => $val) {
        $i++;

        // delete leading zero
        $val = trim($val, '0');

        $change = [
            '.html' => "",
            '-' => " ",
            $cfg["playersName"] => "",
        ];

        $thisLine = explode("_", strtr($val, $change));

        // the opponent playersShow name
        $playerName = $thisLine[2] ?: $thisLine[3];

        $change = [
            '{cfg_link_games}' => $cfg["linkGames"],
            '{val}' => $val,
            '{i}' => $i,
            '{line_0}' => $thisLine[0], // game number
            '{line_1}' => $thisLine[1], // points
            '{opponent}' => $playerName, // opponents name
            '{cntGames}' => $playersShow[$playerName], // cntGames with the opponent
            '{cntPlayerMatches}' => $playersShow[$playerName] === 1 ? 'match' : 'matches',
        ];

        $thisOutput[] = strtr($tplLink, $change);
    }
}

$fullOutput = implode($thisOutput);

// removes all empty space and line breaks with space
$fullOutput = preg_replace('/\s+/', ' ', $fullOutput);


echo str_replace('{fullOutput}', $fullOutput, $tplBody);