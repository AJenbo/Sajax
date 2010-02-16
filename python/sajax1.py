#!/usr/bin/env python
import cgi
import cgitb; cgitb.enable()
import os
import sys
import datetime
import urllib

print "Content-type: text/html"

debug_mode = False
export_list = {}
js_has_been_shown = False

form = cgi.FieldStorage()

def init():
   pass
   
def handle_client_request():
   func_name = form.getfirst('rs')
   if func_name is None:
      return
      
   # Bust cache in the head
   print "Expires: Mon, 26 Jul 1997 05:00:00 GMT"  
   print "Last-Modified: %s GMT" % datetime.datetime.utcnow().strftime("%a, %d %m %H:%M:%S")# always modified
   print "Cache-Control: no-cache, must-revalidate" # HTTP/1.1
   print "Pragma: no-cache"                         # HTTP/1.0
   print
   
   if not func_name in export_list:
      print "-:%s not callable" % func_name
   else:
      print "+:",
      rsargs = form.getlist('rsargs[]')
      result = export_list[func_name](*rsargs)
      print result
   sys.exit()
      
def show_common_js():
   js_debug_mode = str(debug_mode).lower()
   print """\
   	// remote scripting library
		// (c) copyright 2005 modernmethod, inc
		var rs_debug_mode = %(js_debug_mode)s;
		var rs_obj = false;
		var rs_callback = false;
		
		function rs_debug(text) {
			if (rs_debug_mode)
				alert("RSD: " + text)
		}
		
 		function rs_init_object() {
 			rs_debug("rs_init_object() called..")
 			
 			var A;
			try {
				A=new ActiveXObject("Msxml2.XMLHTTP");
			} catch (e) {
				try {
					A=new ActiveXObject("Microsoft.XMLHTTP");
				} catch (oc) {
					A=null;
				}
			}
			if(!A && typeof XMLHttpRequest != "undefined")
				A = new XMLHttpRequest();
			if (!A)
				rs_debug("Could not create connection object.");
			return A;
		}
   """ % locals()
   

def escape(val):
   return val.replace('"', '\\\\"')	

def show_one(func_name):
   uri = os.environ['REQUEST_URI']
   if uri.find('?') == -1:
      uri += "?rs=%s" % urllib.quote_plus(func_name)
   else:
      uri += "&rs=%s" % urllib.quote_plus(func_name)
   escapeduri = escape(uri)
   print """
   // wrapper for %(func_name)s
   function x_%(func_name)s(){
      // count args; build URL
      	var i, x, n;
      	var url = "%(escapeduri)s", a = x_%(func_name)s.arguments;
      	for (i = 0; i < a.length-1; i++) 
      		url = url + "&rsargs[]=" + escape(a[i]);
      	x = rs_init_object();
      	x.open("GET", url, true);
      	x.onreadystatechange = function() {
      		if (x.readyState != 4) 
      			return;
      		rs_debug("received " + x.responseText);
      		
      		var status;
      		var data;
      		status = x.responseText.charAt(0);
      		data = x.responseText.substring(2);
      		if (status == "-") 
      			alert("Error: " + callback_n);
      		else  
      			a[a.length-1](data);
      	}
      	x.send(null);
      	rs_debug("x_%(func_name)s url = " + url);
      	rs_debug("x_%(func_name)s waiting..");
      }
      
      """ % locals()

def export(*args):
   decorated = [(f.func_name, f) for f in args]
   export_list.update(dict(decorated))
     
def show_javascript():
   global js_has_been_shown
   if not js_has_been_shown:
      show_common_js()
      js_has_been_shown = True
   
   for func_name in export_list.iterkeys():
      show_one(func_name)

