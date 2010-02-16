#!/usr/bin/env ioServer

c := CGI clone

multiply := method(x, y,
	x := x asNumber
	y := y asNumber
	return x * y
)

s := Sajax clone
s debug_mode := 1
s init
s export("multiply")
s handle_client_request

write("Content-type: text/html\n\n")

html := """
<html>
<head>
	<title>Multiplier</title>
	<script>"""
write(html)
s show_javascript
html := """
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
"""
write(html)
