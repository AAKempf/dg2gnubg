<?php

$cfg = [];
include '../config.php';
if (file_exists(  "../config.local.php")) {
    include  "../config.local.php";
}

// Latest file
$files = array_slice(scandir($cfg["pathMat"], 1), 2);
rsort($files, SORT_NUMERIC);
$fileTime = filemtime($cfg["pathMat"].$files[0]);

$filesAnalyzed = array_slice(scandir($cfg["pathHtml"], 1), 2);
rsort($filesAnalyzed, SORT_NUMERIC);
$fileAnalyzedTime = filemtime($cfg["pathHtml"].$filesAnalyzed[0]);

$fileDate = date('d.m.Y H:i', $fileTime);
$fileDateAnalyzed = date('d.m.Y H:i', $fileAnalyzedTime);

?><!DOCTYPE html>
<html lang="en" dir="ltr" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <link rel="stylesheet" href="../styles.css" type="text/css"/>
    <title>Tools for Downloading and Writing</title>
</head>
<body>
<h1>Tools</h1>
<p><a href="get_dg_matches.php">Download new *.mat</a> files from dailygammon.com. <br>Latest: <?= $fileDate ?></p>
<?php if ($fileTime > $fileAnalyzedTime): ?>
    <p>Open <a href='write_gnucmd.php'>write_gnucmd</a> to write the gnubg command file.</p>
<?php endif ?>
</body>
</html>