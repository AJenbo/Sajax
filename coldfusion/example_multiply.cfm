<cfinclude template="sajax.cfm">
<cfscript>	
	function multiply(x, y) {
		return x * y;
	}
	
	sajax_init();
	//sajax_debug_mode = 1;:
	sajax_export("multiply");
	sajax_handle_client_request();
	
</cfscript>

<html>
<head>
	<title>Multiplier</title>
	<script>
	<cfscript>sajax_show_javascript();</cfscript>
	
	function do_multiply_cb(z) {
		document.getElementById("z").value = z;
	}
	
	function do_multiply() {
		// get the folder name
		var x, y;
		
		x = document.getElementById("x").value;
		y = document.getElementById("y").value;
		x_multiply(x, y, do_multiply_cb);
	}
	</script>
	
</head>
<body>
	<input type="text" name="x" id="x" value="2" size="3">
	*
	<input type="text" name="y" id="y" value="3" size="3">
	=
	<input type="text" name="z" id="z" value="" size="3">
	<input type="button" name="check" value="Calculate"
		onclick="do_multiply(); return false;">
</body>
</html>
