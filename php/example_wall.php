<?
	require("Sajax.php");
	
	//
	// The world's least efficient wall implementation
	//
	
	function colorify_ip($ip)
	{
		$parts = explode(".", $ip);
		$color = sprintf("%02s", dechex($parts[1])) .
				 sprintf("%02s", dechex($parts[2])) .
				 sprintf("%02s", dechex($parts[3]));
		return $color;
	}
	
	function add_line($msg) {
		$f = fopen("/tmp/wall.html", "a");
		$dt = date("Y-m-d h:i:s");
		$msg = strip_tags(stripslashes($msg));
		$remote = $_SERVER["REMOTE_ADDR"];
		// generate unique-ish color for IP
		$color = colorify_ip($remote);
		fwrite($f, "<span style=\"color:#$color\">$dt</span> $msg<br>\n");
		fclose($f);
	}
	
	function refresh() {
		$lines = file("/tmp/wall.html");
		// return the last 25 lines
		return join("\n", array_slice($lines, -25));
	}
	
	$sajax_request_type = "GET";
	sajax_init();
	sajax_export("add_line", "refresh");
	sajax_handle_client_request();
	
?>
<html>
<head>
	<title>Wall</title>
	<style>
	.date { 
		color: blue;
	}
	</style>
	<script>
	<?
	sajax_show_javascript();
	?>
	
	var check_n = 0;
	
	function refresh_cb(new_data) {
		document.getElementById("wall").innerHTML = new_data;
		document.getElementById("status").innerHTML = "Checked #" + check_n++;
		setTimeout("refresh()", 1000);
	}
	
	function refresh() {
		document.getElementById("status").innerHTML = "Checking..";
		x_refresh(refresh_cb);
	}
	
	function add_cb() {
		// we don't care..
	}

	function add() {
		var line;
		var handle;
		handle = document.getElementById("handle").value;
		line = document.getElementById("line").value;
		if (line == "") 
			return;
		x_add_line("[" + handle + "] " + line, add_cb);
		document.getElementById("line").value = "";
	}
	</script>
	
</head>
<body onload="refresh();">

<form name="f" action="#" onsubmit="add();return false;">
	<a href="http://www.modernmethod.com/sajax">Sajax</a>
	- 
	This example illustrates the simplest possible graffiti wall.
	It isn't meant to be perfect, featureful, or even useful.<br/>
	
	<input type="text" name="handle" id="handle" value="(name)"
		onfocus="this.select()" style="width:130px;">
	<input type="text" name="line" id="line" value="(enter your message here)"
		onfocus="this.select()"
		style="width:300px;">
	<input type="button" name="check" value="Post message"
		onclick="add(); return false;">
	<div id="wall"></div>
	<div id="status"><em>Loading..</em></div>
</form>
	
</body>
</html>
