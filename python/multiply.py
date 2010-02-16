#!/usr/bin/env python
import cgitb;cgitb.enable()
import sajax1

def multiply(x,y):
   try:
      float_x, float_y = float(x), float(y)
   except:
      return 0
   return float_x * float_y

sajax1.init()
sajax1.export(multiply)
sajax1.handle_client_request()

print """
<html>
<head>
	<title>PyMuptiplier</title>
	<script>
"""
sajax1.show_javascript()
print """
	function set_math_result(result) {
		document.getElementById("z").value = result;
	}
	function do_the_math() {
		var x, y;
		x = document.getElementById("x").value;
		y = document.getElementById("y").value;
		// our php function multiply() has been
		// linked to a javascript function named
		// x_multiply(). call it.
		x_multiply(x, y, set_math_result);
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
		onclick="do_the_math(); return false;">
</body>
</html>
""" % locals()