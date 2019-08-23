<!DOCTYPE html>
<html lang="en" dir="ltr" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>Search Form</title>
    <link rel="stylesheet" href="styles.css" type="text/css"/>
</head>
<body>
<form method="post" action="browse.php" target="browse">
    <select id="points" name="p" title="Points">
        <option value="">pts
        <option value="01p"> 1
        <option value="03p"> 3
        <option value="05p"> 5
        <option value="07p"> 7
        <option value="09p"> 9
        <option value="11p">11
        <option value="13p">13
        <option value="15p">15
        <option value="17p">17
        <option value="19p">19
        <option value="21p">21
        <option value="23p">23
        <option value="25p">25
    </select>
    <input type="text" name="s" title="Search for name or empty search for full list" placeholder="Search">
    <input type="submit" value="go"> <a href="browse.php" target="browse" class="utf8-icons ml6">&#x21bb;</a>
</form>
</body>
</html>
