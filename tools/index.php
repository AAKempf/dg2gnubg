<?php

include '../config.php';

// Latest file
$files = scandir($cfg["path_mat"], SCANDIR_SORT_ASCENDING);
$file_time = filectime($cfg["path_mat"] . $files[0]);

$files_analyzed = scandir($cfg["path_html"], SCANDIR_SORT_ASCENDING);
$file_analyzed_time = filectime($cfg["path_html"] . $files_analyzed[0]);

$file_date = date('d.m.Y H:i', $file_time);
$file_date_analyzed = date('d.m.Y H:i', $file_analyzed_time);

?><!DOCTYPE html>
<html lang="en" dir="ltr" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <link rel="stylesheet" href="../styles.css" type="text/css"/>
    <title>Tools for Downloading and Writing</title>
</head>
<body>
<h1>Tools</h1>
<p><a href="get_dg_matches.php">Download new *.mat</a> files from dailygammon.com. <br>Latest: <?= $file_date ?></p>
<?php if ($file_time > $file_analyzed_time): ?>
    <p>Open <a href='write_gnucmd.php'>write_gnucmd</a> to write the gnubg command file.</p>
<?php endif ?>
</body>
</html>