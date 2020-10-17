<?php
/**
 * Writes cmdGnuBg file for gnubg from saved dailygammon matches
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
$cfg = [];
include "../config.php";
if (file_exists(  "../config.local.php")) {
    include  "../config.local.php";
}


// Start with the work
// *******************
// get directory handle
$dp = opendir($cfg["pathMat"]);

// read directory and build an array of values
// *******************************************

// contains all .mat file names later on
$matchNumbers = [];

// contains all match infos later on
$matchInfos = [];

$i = 0;
while (($file = readdir($dp)) !== false) {
    if ($file !== "." && $file !== ".."
        && false !== strpos($file, ".mat")
        && filesize($cfg["pathMat"] . $file) > 200
    ) {

        // file date
        $matchInfos[$i]["fd"] = date("Y-m-d", fileatime($cfg["pathMat"] . $file));

        // {fn}
        $matchNumbers[$i] = str_replace(".mat", "", $file);

        // file handle
        $fp = fopen($cfg["pathMat"] . $file, "rb");

        $firstName = "";

        $k = 0;
        while ($k < 4 && !feof($fp)) {
            /*           1    1    2    2    3    3    4
               0----5----0----5----0----5----0----5----0----x----x
             0|5 point match
             1|
             2|Game 1
             3|victory1 : 0                        amalesh : 0
            */
            $lineMatch = trim(fgets($fp, 72));

            switch ($k) {
                case "0": // {mp}
                    $matchInfos[$i]["mp"] = trim(substr($lineMatch, 0, 2));

                    if (strlen($matchInfos[$i]["mp"]) === 1) {
                        $matchInfos[$i]["mp"] = "0" . $matchInfos[$i]["mp"];
                    }
                    break;

                case "3": // {p1} {p2}
                    // Get the first name and reduce line to second name

                    // get the firstName
                    $lenLine = strlen($lineMatch);
                    for ($x = 0; $x < $lenLine; $x++) {
                        $sTmp = $lineMatch[$x];
                        if ($sTmp !== ":") {
                            $firstName .= $sTmp;
                        } else {
                            $x = $lenLine;
                        }
                    }
                    $lineMatch = str_replace([" : 0", $firstName], "", $lineMatch);

                    // replace double empty spaces
                    $lineMatch = preg_replace('/\s+/', ' ', $lineMatch);

                    $matchInfos[$i]["p1"] = trim($firstName);
                    $matchInfos[$i]["p2"] = trim($lineMatch);

                    if (count($cfg["replaceNameFrom"])) {
                        if (in_array($matchInfos[$i]["p1"], $cfg["replaceNameFrom"], true)) {
                            $matchInfos[$i]["p1"] = str_replace($cfg["replaceNameFrom"], $cfg["replaceNameTo"],
                                $matchInfos[$i]["p1"]);
                        }
                        if (in_array($matchInfos[$i]["p2"], $cfg["replaceNameFrom"], true)) {
                            $matchInfos[$i]["p2"] = str_replace($cfg["replaceNameFrom"], $cfg["replaceNameTo"],
                                $matchInfos[$i]["p2"]);
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
        if (isset($cfg["ignorePlayers"]) && in_array($firstName, $cfg["ignorePlayers"], true)) {
            unset($matchInfos[$i]);
        }
        // check for games to ignore / skip
        if (isset($cfg["ignoreGames"]) && in_array($matchInfos[$i]["mp"], $cfg["ignoreGames"], true)) {
            unset($matchInfos[$i]);
        }

        if (isset($matchInfos[$i])) {
            $i++;
        }
    }
}
// close directory
closedir($dp);

// read thisOutput directory to pretend double analyses
$dp = opendir($cfg["pathHtml"]);

// analyzed matches
$analyzeExists = [];

while (($fileAnalyze = readdir($dp)) !== false) {
    if ($fileAnalyze !== "." && $fileAnalyze !== ".." && false !== strpos($fileAnalyze, ".html")) {
        // check if match number (filename) is in older thisOutput file name
        foreach ($matchNumbers as $key => $val) {
            // matches
            if ($val && false !== strpos($fileAnalyze, $val)) {
                $analyzeExists[$key] = $val;
            }
        }

        reset($matchNumbers);
    }
}
// html file already exists
if (count($analyzeExists)) {
    // remove double values
    $analyzeExists = array_unique($analyzeExists);

    // get only new filesShow whit no thisOutput so far
    $analyzeNew = array_diff($matchNumbers, $analyzeExists);
} else {
    $analyzeNew = $matchNumbers;
}

// build the gnubg commands
// ************************
$cmdGnuBg = $cfg["cmdGnuBg"];
if (DIRECTORY_SEPARATOR === "\\") {
    $cmdGnuBg = $cfg["cmdGnuBgWin"];
}

$cmdLine = "# commands for {$cmdGnuBg}" . $cfg["iniHeadGnuBg"];

foreach ($analyzeNew as $key => $val) {

    // Export file name
    // $cfg["patternFileName"] = "{fn}_{mp}p_{p1}_{p2}";

    // replace spaces in player name
    $player1 = str_replace(" ", "-", $matchInfos[$key]["p1"]);
    $player2 = str_replace(" ", "-", $matchInfos[$key]["p2"]);

    $change = [
        "{mp}" => $matchInfos[$key]["mp"],
        "{fn}" => $analyzeNew[$key],
        "{fd}" => $matchInfos[$key]["fd"],
        "{p1}" => $player1,
        "{p2}" => $player2,
    ];

    // without extensions, will be added later
    $exportFile = strtr($cfg["patternFileName"], $change);

    $cmdLine .= "# "
        . $matchInfos[$key]["mp"]
        . " point match: "
        . $matchInfos[$key]["p1"]
        . " vs. "
        . $matchInfos[$key]["p2"]
        . "\n";

    $change = [
        "{pathMat}" => $cfg["pathMat"],
        "{file_mat}" => $analyzeNew[$key] . ".mat",
        "{pathHtml}" => $cfg["pathHtml"],
        "{file_html}" => $exportFile . ".html",
        "{pathSgf}" => $cfg["pathSgf"],
        "{file_sgf}" => $exportFile . ".sgf",
        "{pathPdf}" => $cfg["pathPdf"],
        "{file_pdf}" => $exportFile . ".pdf",
    ];

    if (isset($cfg["iniListGnuBg"])) {
        foreach ($cfg["iniListGnuBg"] as $key2 => $val2) {

            if (false !== strpos($val2, "{")) {
                $val2 = strtr($val2, $change);
            }
            $cmdLine .= $val2 . PHP_EOL;
        }
    }
}

// for use from shell, see workaround.sh
if (!empty($argv[1])) {
    parse_str($argv[1], $_GET);
}

// write the command file
// **********************
$cmdWritten = false;
if (count($analyzeNew)) {
    $fp = fopen($cfg["pathGnuBgIni"], "wb");
    $cmdWritten = fwrite($fp, $cmdLine);
    fclose($fp);
}

// write_gnucmd.php?write=1&cli=1
// to write the cmdGnuBg file only and suppress the thisOutput
if (isset($_GET["cli"])) {
    exit;
}

?><!DOCTYPE html>
<html lang="en" dir="ltr" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <link rel="stylesheet" href="../styles.css" type="text/css"/>
    <title>Writes command file for GnuBG from saved backgammon matches</title>
</head>

<body>
<h1>Writes command file for GnuBG from saved backgammon matches</h1>

<p>You have <?= count($analyzeExists) ?> analyzed and <?= count($analyzeNew) ?> non-analyzed matches</p>

<?php
if ($cmdWritten) { ?>
    <h3>GnuBGcommand file successfully written</h3>
    <p>Open your terminal and copy & paste the following line to it:</p>
    <pre><?= $cmdGnuBg . " -c " . $cfg["pathGnuBgIni"] ?></pre>
    <p>The content of the ini-file:</p>
    <pre><?= $cmdLine ?></pre>
<?php } else { ?>
    <h3>Error writing GnuBG command file, maybe all .mat-files have been analyzed</h3>
    <p>Open <a href="../index.php">analyzed matches</a> or check for <a href="get_dg_matches.php">finished one</a></p>
<?php } ?>

</body>
</html>
