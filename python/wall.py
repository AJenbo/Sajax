#!/usr/bin/env python
import cgi
import cgitb;cgitb.enable()
import datetime
import os

import sajax1

WALLFILE = '/tmp/wall.html'

if not os.path.exists(WALLFILE):
   fh = open(WALLFILE, 'w')
   fh.close()

def colourify_ip(ip):
   colour = ''.join(['%02x' % int(part) for part in ip.split('.')[-3:]])
   return colour

def add_line(msg):
   f = open("/tmp/wall.html","a")
   datetime = datetime.datetime.utcnow().strftime("%Y-%m-%d %H:%M:%S")
   msg = cgi.escape(msg)
   remote = os.environ['REMOTE_ADDR']
   colour = colourify_ip(remote)
   f.write('<span style="color:#%(colour)s">%(datetime)s</span> %(msg)s<br />\n' % locals())
   f.close()

def refresh():
   f = open("/tmp/wall.html")
   return '\n'.join(list(f)[-25:])

sajax1.sajax_init()
sajax1.sajax_export(refresh, add_line)
sajax1.sajax_handle_client_request()

print """
<html>
<head>
	<title>PyWall</title>
	<script>
"""
sajax1.sajax_show_javascript()
print """
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

	<a href="http://">Sajax</a> - Wall Example<br/>

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
""" % locals()
