<?
	require("incl_sajax.php");
	
	// the world's least efficient wall
	function add_line($msg) {
		
		$f = fopen("/tmp/wall.html", "a");
		$dt = date("Y-m-d h:i:s");
		$msg = strip_tags($msg);
		$remote = $_SERVER["REMOTE_ADDR"];
		fwrite($f, "<!-- $remote --><b>$dt</b> $msg<br>\n");
		fclose($f);
	}
	
	function refresh() {
		$f = fopen("/tmp/wall.html", "r");
		$lines = array();
		while (!feof($f)) 
			$lines[] = fgets($f, 8192);
		// return the last 15 lines
		return join("\n", array_slice($lines, -25));
	}
	
	rs_init();
	// $rs_debug_mode = 1;
	rs_export("add_line", "refresh");
	rs_handle_client_request();
	
?>
<html>
<head>
	<title>Wall</title>
	<script>
	<?
	rs_show_javascript();
	?>
	
	function refresh_cb(new_data) {
		document.getElementById("wall").innerHTML = new_data;
		document.getElementById("status").innerHTML = "Checked";
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
		x_add_line("[" + handle + "] " + line, add_cb);
		document.getElementById("line").value = "";
	}
	</script>
	
</head>
<body onload="refresh();">

	<input type="text" name="handle" id="handle" value="(name)"
		onfocus="this.select()" style="width:130px;">
	<input type="text" name="line" id="line" value="(enter your message here)"
		onfocus="this.select()"
		style="width:300px;">
	<input type="button" name="check" value="Post message"
		onclick="add(); return false;">
	<div id="wall"></div>
	<div id="status"><em>Loading..</em></div>
	
</body>
</html>
