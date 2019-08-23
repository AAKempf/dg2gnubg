<?php

// needed only here to save file names
session_start();


include __DIR__ . "/config.php";


$tpl_title = "<strong>{cnt_matches} {match_plural}, {cnt_games} games, {player_info}</strong>";

// template for the link // ,&#10;analyzed on {file_date}
$tpl_link = '
   <a href="{cfg_link_games}{val}" title="Match #{line_0}" target="games">{line_0}</a> 
   <a href="browse.php?p={line_1}" title="Matches with {line_1}" class="ml6">{line_1}</a> 
   <a href="browse.php?s={opponent}" title="{cnt_games} {cnt_matches} with {opponent}" class="ml6">{opponent}</a><br />';


$form_fields = $_POST;
if (!$form_fields) {
    $form_fields = $_GET;
}
// the match files we will show
$files = [];
// the match files to hide
$files_hidden = [];

if (isset($_SESSION["files"])) {
    $files = $_SESSION["files"];
    $files_hidden = $_SESSION["files_hidden"];
} else {
    //get directory handle
    $dir = opendir($cfg["path_html"]);

    // read the analyzed file names
    while (($file = readdir($dir)) !== false) {
        if ($file !== "." && $file !== ".."
            && false !== strpos($file, ".html")
            && !strpos_array($file, $cfg["ignore_players"])) {

            if ($file[strlen($file) - 9] !== "_") {
                // trick to add a leading zero for 1-9pointer
                if ($file[6] === "_") {
                    $file = "0" . $file;
                }
                $files[] = $file;
            } // end of file like "_002.html"
            else {
                $files_hidden[] = $file;
            }
        }
    }
    //close directory
    closedir($dir);

    rsort($files);
    reset($files);
}
if (!isset($_SESSION["files"]) && count($files)) {
    $_SESSION["files"] = $files;
}
if (!isset($_SESSION["files_hidden"]) && count($files_hidden)) {
    $_SESSION["files_hidden"] = $files_hidden;
}

// search stuff
if ($form_fields) {

    if (array_key_exists("p", $form_fields)) {
        $form_fields["p"] = strtolower($form_fields["p"]); // points
        $form_fields["p"] = preg_replace('/[^0-9p]/', "", $form_fields["p"]);
    }

    if (array_key_exists("s", $form_fields)) {
        $form_fields["s"] = strtolower($form_fields["s"]); // player names
        $form_fields["s"] = str_replace(" ", "-", $form_fields["s"]);
    }
}

// The file names in array depending on the search
$links = [];
if ($form_fields["s"] || $form_fields["p"]) {
    foreach ($files as $key => $val) {

        $check = strtolower($val);

        if ($found = GetFileByField($form_fields, $check, $val)) {
            $links[] = $found;
        }
    }
} else {
    $links = $files;
}

// get amount of matches with opponents
$players = [];
foreach ($links as $key => $val) {

    // delete leading zero
    $val = trim($val, '0');

    $change = [
        '.html' => "",
        '-' => " ",
        $cfg["players_name"] => "", // removes the name
    ];

    $this_line = explode("_", strtr($val, $change));
    // the opponent players name
    $player_name = $this_line[2] ?: $this_line[3];

    $players[$player_name]++;
}

// Just to count later on the hidden files per search
$hidden_files = [];

if (count($files_hidden)) {

    if ($form_fields["p"] || $form_fields["s"]) {
        foreach ($files_hidden as $key => $val) {

            $check = strtolower($val);

            if ($found = GetFileByField($form_fields, $check, $val)) {
                $hidden_files[] = $found;
            }
        }
    } else {
        $hidden_files = $files_hidden;
    }
}

$cnt_games = count($hidden_files) + count($links);
$cnt_matches = count($links);
$cnt_players = count($players);

$output[] = ""; // output

if ($cnt_matches) {

    $change = [
        '{cnt_games}' => $cnt_games,
        '{cnt_matches}' => $cnt_matches,
        '{match_plural}' => $cnt_matches === 1 ? 'match' : 'matches',
        '{player_info}' => $cnt_players > 1 ? '<br>' . $cnt_players . ' players' : '',
    ];

    $output[] = strtr($tpl_title, $change);
}

$output[] = '<hr>';

if (is_array($links)) {

    foreach ($links as $key => $val) {
        $i++;

        // delete leading zero
        $val = trim($val, '0');

        $change = [
            '.html' => "",
            '-' => " ",
            $cfg["players_name"] => "",
        ];

        $this_line = explode("_", strtr($val, $change));

        // the opponent players name
        $player_name = $this_line[2] ?: $this_line[3];

        $change = [
            '{cfg_link_games}' => $cfg["link_games"],
            '{val}' => $val,
            '{i}' => $i,
            '{line_0}' => $this_line[0], // game number
            '{line_1}' => $this_line[1], // points
            '{opponent}' => $player_name, // opponents name
            '{cnt_games}' => $players[$player_name], // cnt_games with the opponent
            '{cnt_matches}' => $players[$player_name] === 1 ? 'match' : 'matches',
        ];

        $output[] = strtr($tpl_link, $change);

    }
}
// removes all empty space and line breaks with space
$output = preg_replace('/\s+/', ' ', $output);

?><!DOCTYPE html>
<html lang="en" dir="ltr" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>Browse</title>
    <link rel="stylesheet" href="styles.css" type="text/css"/>
    <style type="text/css">
        body {
            margin: 0 0 0 2px;
        }
    </style>
</head>
<body class="fontfamily">
<?= implode($output) ?>
</body>
</html>