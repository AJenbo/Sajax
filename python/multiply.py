#!/usr/bin/env python
import cgitb;cgitb.enable()
import sajax1

def multiply(x,y):
   try:
      float_x, float_y = float(x), float(y)
   except:
      return 0
   return float_x * float_y

sajax1.sajax_init()
sajax1.sajax_export(multiply)
sajax1.sajax_handle_client_request()

print """
<html>
<head>
	<title>PyMultiplier</title>
	<script>
"""
sajax1.sajax_show_javascript()
print """
	function do_multiply_cb(z) {
		document.getElementById("z").value = z;
	}
	function do_multiply() {
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
""" % locals()
