<?
	require("incl_sajax.php");
	
	// the world's least efficient wall
	function add_line($msg) {
		global $REMOTE_ADDR;
		
		$f = fopen("/tmp/wall.html", "a");
		$dt = date("Y-m-d h:i:s");
		fwrite($f, "<!-- $REMOTE_ADDR --><b>$dt</b> $msg<br>\n");
		fclose($f);
	}
	
	function refresh() {
		$f = fopen("/tmp/wall.html", "r");
		$lines = array();
		while (!feof($f)) 
			$lines[] = fgets($f, 8192);
		// return the last 15 lines
		return join("\n", array_slice($lines, -15));
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
	}
	
	function refresh() {
		document.getElementById("status").innerHTML = "Checking..";
		x_refresh(refresh_cb);
		setTimeout("refresh()", 2500);
	}
	
	function add_cb() {
		// we don't care..
	}
	
	function add() {
		var line;
		line = document.getElementById("line").value;
		x_add_line(line, add_cb);
		document.getElementById("line").value = "";
		refresh();
	}
	</script>
	
</head>
<body onload="refresh();">

	<input type="text" name="line" id="line" value="(enter your message here)"
		onfocus="this.select()" style="width:300px;">
	<input type="button" name="check" value="Post message"
		onclick="add(); return false;">
	<div id="wall"></div>
	<div id="status"><em>Loading..</em></div>
	
</body>
</html>
