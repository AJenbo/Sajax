<%@ Language=JScript %>
<!--#include file="sajax.asp"-->

<%
	
	function multiply(x, y) {
        return x * y;
	}
	
	function add(x, y, z) {
        return x + y + z;
	}
    
	sajax = new Sajax();
//	sajax.debug_mode = true;
	sajax.export_function("multiply","add");
	sajax.handle_client_request();

%>
<html>
<head>
	<title>Multiplier</title>
	<script>
	<%
	sajax.show_javascript();
	%>
	
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
    
	function do_add_cb(z) {
		document.getElementById("zza").value = z;
	}

	function do_add() {
		// get the folder name
		var x, y, z;
		
		x = "'"+document.getElementById("xa").value+"'";
		y = "'"+document.getElementById("ya").value+"'";
        z = "'"+document.getElementById("za").value+"'";
		x_add(x, y, z, do_add_cb);
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
        <br>
        try adding some strings, eg:
	<input type="text" name="xa" id="xa" value="ex " size="3">
	+
	<input type="text" name="ya" id="ya" value="nihil, " size="3">
	+
	<input type="text" name="za" id="za" value="nihil fit" size="3">
	=
	<input type="text" name="zza" id="zza" value="" size="15">
	<input type="button" name="check" value="Calculate"
		onclick="do_add(); return false;">        
</body>
</html>
