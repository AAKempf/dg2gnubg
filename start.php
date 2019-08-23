<?php

include __DIR__ . "/config.php";

// template for the players link
$tpl_player_link = '<a href="{cfg_dg_profile}" title="dailygammon profile" target="_blank">{cfg_players_name}</a>';

$players_profile = "";

if ($cfg["dg_profile"] > '') {

    $change = [
        '{cfg_dg_profile}' => $cfg["dg_profile"],
        '{cfg_players_name}' => $cfg["players_name"],
    ];

    $players_profile = strtr($tpl_player_link, $change);
}

$files_analyzed = scandir($cfg["path_html"], SCANDIR_SORT_ASCENDING);
$file_date = date('d.m.Y', filectime($cfg["path_html"] . $files_analyzed[0]));

?><!DOCTYPE html>
<html lang="en">
<head>
    <title>Start</title>
    <link rel="stylesheet" href="styles.css" type="text/css"/>
</head>
<body>
<h1>Exported and analyzed dailygammon.com matches</h1>

<div style="float:right;padding:0 0 0 10px">
    <a href="http://www.gnubg.org/" target="_blank" title="www.gnubg.org">
        <img src="gnubackgammon.jpg" alt="gnubg" width="163" height="313"></a>
</div>
<div>
    <a href="http://www.dailygammon.com" target="_blank" title="www.dailygammon.com">
        <img src="dglogo.gif" width="255" height="83" alt="www.dailygammon.com"></a>
</div>
<div class="clearfix">
    <p>Here are all backgammon matches played by <?= $players_profile ?> on
        <a href="http://www.dailygammon.com" target="_blank">dailygammon</a>,
        analyzed and exported by
        <a href="http://www.gnubg.org/" target="_blank" title="www.gnubg.org">GNU Backgammon</a>.
    </p>
    <p>The newest game is from <?= $file_date ?></p>

    <p>The matches are sorted by number. Next to it is the match length with the player name.</p>

    <p>Clicking on the</p>
    <ul>
        <li>number opens the game,</li>
        <li>on the match length opens all games of this length and</li>
        <li>on the player name opens all matches with this opponent.
        </li>
    </ul>

    <p>Note: If you are a DG player and don't want to see your games here, please send me a short message on DG.</p>

    <p>The used settings for GNU Backgammon:</p>

    <pre><?= $cfg["gnubg_ini_head"] ?></pre>
    <pre><?= implode(PHP_EOL, $cfg["gnubg_ini_list"]) ?></pre>

    <p>If you want to use these tools for yourself, you can download them from
        <a href="https://github.com/AAKempf/dg2gnubg" target="_blank">GitHub</a></p>

    <p>Btw: <a href="openings.php">Starting rolls</a> and the best moves.</p>
</div>
</body>
</html>
