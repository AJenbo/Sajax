<?php
/**
 * The world's least efficient wall implementation (now a bit more efficient)
 */

// Set default time zone
date_default_timezone_set('Europe/Copenhagen');

// File contaning messages up til now
$filename = 'tmp/wall.html';

/**
 * Convert ip to a hex color, non reversably
 *
 * @param string $ipAddress Ip adds to convert
 *
 * @return string
 */
function colorifyIp(string $ipAddress)
{
    $parts = explode('.', $ipAddress);
    $color = sprintf('%02s', dechex($parts[1]))
        . sprintf('%02s', dechex($parts[2]))
        . sprintf('%02s', dechex($parts[3]));
    return $color;
}

/**
 * Post a message and get changes since last request
 *
 * @param string $msg Message to post
 *
 * @return array|false
 */
function addLine(string $msg)
{
    global $filename;
    $file = fopen($filename, 'a');
    $date = date('Y-m-d h:i:s');
    $msg = strip_tags(stripslashes($msg));
    $remote = $_SERVER['REMOTE_ADDR'];
    // generate unique-ish color for IP
    $color = colorifyIp($remote);
    fwrite($file, '<span style="color:#' . $color . '">' . $date . '</span> '
        . htmlspecialchars($msg) . '<br />' . "\r\n");
    fclose($file);
    return refresh(0);
}

/**
 * Get changes since last request
 *
 * @param int $lastrefresh Time of last request
 *
 * @return array|false
 */
function refresh(int $lastrefresh)
{
    global $filename;
    if (filemtime($filename) > $lastrefresh) {
        $lines = file($filename);
        // return the last 25 lines
        return [
            'wall' => join("\n", array_slice($lines, -25)),
            'update' => filemtime($filename)
        ];
    }

    return false;
}

// Include the libery
require_once 'Sajax.php';
// Set redirect page in case of error
Sajax\Sajax::$failureRedirect = '/sajaxfail.html';
// Export methodes
Sajax\Sajax::export(
    [
        'addLine' => ['method' => 'POST'],
        'refresh'  => ['method' => 'GET'],
    ]
);
// Handel the ajax request, script will exit here on ajax calls
Sajax\Sajax::handleClientRequest();

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Sajax graffiti wall example</title>
<style type="text/css">
.date {
    color: blue;
}
</style>
<script type="text/javascript" src="json_stringify.js"></script>
<script type="text/javascript" src="json_parse.js"></script>
<script type="text/javascript" src="sajax.js"></script>
<script type="text/javascript"><!--
<?php Sajax\Sajax::showJavascript(); ?>

var check_n = 1;
/**
 * Keeps track of next call for enw messages
 */
var nextrefresh;
/**
 * Insert new messages
 */
function refresh_cb(data) {
    if(data !== false) {
        document.getElementById("wall").innerHTML = data["wall"];
        lastrefresh = data["update"];
        clearTimeout(nextrefresh);
        nextrefresh = setTimeout("refresh();", 1000);
    } else {
        clearTimeout(nextrefresh);
        // It's a slow day, lower the check rate
        nextrefresh = setTimeout("refresh();", 2500);
    }
    document.getElementById("status").innerHTML = "Checked #" + check_n++;
}

var lastrefresh = <?php echo(filemtime($filename)); ?>;
/**
 * Get latest messages
 */
function refresh() {
    document.getElementById("status").innerHTML = "Checking..";
    x_refresh(lastrefresh, refresh_cb);
}

/**
 * Add new message
 */
function add() {
    var line;
    var handle;
    handle = document.getElementById("handle").value;
    line = document.getElementById("line").value;
    if(line == "")
        return;
    x_addLine("[" + handle + "] " + line, refresh_cb);
    document.getElementById("line").value = "";
}

/**
 * Listen for enter and post message
 */
function keypress(keyCode) {
    if (keyCode==13) {
        add();
        document.getElementById("line").select();
        return false;
    }
    return true;
}

// Check for new messages
nextrefresh = setTimeout("refresh();", 1000);
//-->
</script>
</head>
<body>
<b><a href="https://github.com/AJenbo/Sajax">Sajax</a> v<?php echo($sajax_version); ?></b> - You are a guinea pig - This example illustrates the simplest possible graffiti wall. It isn't meant to be perfect, featureful, or even useful.<br />
<form action="" method="post" onsubmit="add(); return false;">
    <input type="text" name="handle" id="handle" value="(name)" onfocus="this.select()" style="width:130px;" />
    <input type="text" name="line" id="line" value="(enter your message here)" onfocus="this.select();" onkeypress="keypress(event.keyCode);" style="width:300px;" />
    <input type="button" name="check" value="Post message" onclick="add(); return false;" />
</form>
<div id="wall"> <?php $temp = refresh(0);
// Pre-load initial messages
echo($temp['wall']); ?></div>
<div id="status">Checked #0</div>
</body>
</html>
