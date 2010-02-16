<?php
	//
	// The world's least efficient wall implementation (now a bit more efficient)
	//
	
	date_default_timezone_set('Europe/Copenhagen');
	$filename = "tmp/wall.html";
	
	function colorify_ip($ip)
	{
		$parts = explode(".", $ip);
		$color = sprintf("%02s", dechex($parts[1])) .
				 sprintf("%02s", dechex($parts[2])) .
				 sprintf("%02s", dechex($parts[3]));
		return $color;
	}
	
	function add_line($msg) {
		global $filename;
		$f = fopen($filename, "a");
		$date = date("Y-m-d h:i:s");
		$msg = strip_tags(stripslashes($msg));
		$remote = $_SERVER["REMOTE_ADDR"];
		// generate unique-ish color for IP
		$color = colorify_ip($remote);
		fwrite($f, '<span style="color:#'.$color.'">'.$date.'</span> '.htmlspecialchars($msg).'<br />'."\r\n");
		fclose($f);
		return refresh(0);
	}
	
	function refresh($lastrefresh) {
		global $filename;
		if(filemtime($filename) > $lastrefresh) {
			$lines = file($filename);
			// return the last 25 lines
			return array("wall" => join("\n", array_slice($lines, -25)), "update" => filemtime($filename));
		} else {
			return false;
		}
	}
	
	require("sajax.php");
//	$sajax_debug_mode = true;
	$sajax_failure_redirect = "http://sajax.info/sajaxfail.html";
	sajax_export(
		array("name" => "add_line", "method" => "POST"),
		array("name" => "refresh", "method" => "GET")
	);
	sajax_handle_client_request();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
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
	<?php sajax_show_javascript(); ?>
	
	var check_n = 1;
	var nextrefresh;
	function refresh_cb(data) {
		if(data !== false) {
			document.getElementById("wall").innerHTML = data["wall"];
			lastrefresh = data["update"];
			clearTimeout(nextrefresh);
			nextrefresh = setTimeout("refresh();", 1000);
		} else {
			clearTimeout(nextrefresh);
			nextrefresh = setTimeout("refresh();", 2500);
		}
		document.getElementById("status").innerHTML = "Checked #" + check_n++;
	}
	
	var lastrefresh = <?php echo(filemtime($filename)); ?>;
	function refresh() {
		document.getElementById("status").innerHTML = "Checking..";
		x_refresh(lastrefresh, refresh_cb);
	}
	
	function add() {
		var line;
		var handle;
		handle = document.getElementById("handle").value;
		line = document.getElementById("line").value;
		if(line == "")
			return;
		x_add_line("[" + handle + "] " + line, refresh_cb);
		document.getElementById("line").value = "";
	}
	
	function keypress(keyCode) {
		if (keyCode==13) {
			add();
			document.getElementById("line").select();
			return false;
		}
		return true;
	}
	
	nextrefresh = setTimeout("refresh();", 1000);
	//-->
</script>
</head>
<body>
<b><a href="http://www.sajax.info/">Sajax</a> v<?php echo($sajax_version); ?></b> - You are a guinea pig - This example illustrates the simplest possible graffiti wall. It isn't meant to be perfect, featureful, or even useful.<br />
<form action="" method="post" onsubmit="add(); return false;">
	<input type="text" name="handle" id="handle" value="(name)" onfocus="this.select()" style="width:130px;" />
	<input type="text" name="line" id="line" value="(enter your message here)" onfocus="this.select();" onkeypress="keypress(event.keyCode);" style="width:300px;" />
	<input type="button" name="check" value="Post message" onclick="add(); return false;" />
</form>
<div id="wall"> <?php $temp = refresh(0); echo($temp["wall"]); ?></div>
<div id="status">Checked #0</div>
</body>
</html>
