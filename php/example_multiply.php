<?php
	function multiply($x, $y) {
		return $x * $y;
	}
	
	require("sajax.php");
//	$sajax_debug_mode = true;
	$sajax_failure_redirect = "http://sajax.info/sajaxfail.html";
	sajax_export("multiply");
	sajax_handle_client_request();
	 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Sajax Multiplier example</title>
<script type="text/javascript" src="json2.stringify.js"></script>
<script type="text/javascript" src="json_stringify.js"></script>
<script type="text/javascript" src="json_parse_state.js"></script>
<script type="text/javascript" src="sajax.js"></script>
<script type="text/javascript"><!--
	<?php sajax_show_javascript(); ?>
	
	function do_multiply_cb(z) {
		document.getElementById("z").value = z;
	}
	
	function do_multiply() {
		// get the folder name
		var x, y;
		
		x = parseFloat(document.getElementById("x").value);
		y = parseFloat(document.getElementById("y").value);
		x_multiply(x, y, do_multiply_cb);
	}
	//-->
</script>
</head>
<body>
<input type="text" name="x" id="x" value="2" size="3" />
*
<input type="text" name="y" id="y" value="3" size="3" />
=
<input type="text" name="z" id="z" value="" size="3" />
<input type="button" name="check" value="Calculate" onclick="do_multiply(); return false;" />
</body>
</html>
