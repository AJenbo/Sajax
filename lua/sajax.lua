module ("sajax")

local export_list = {}
local request_uri = ""

function init ()
end

function handle_client_request ()

	if not cgi.rs then return end
	
	-- Bust cache in the head
	cgilua.header ("Expires", "Mon, 26 Jul 1997 05:00:00 GMT")    -- Date in the past
	cgilua.header ("Last-Modified", os.date ("!%a, %d %b %Y %H:%M:%S GMT"))
	-- always modified
	cgilua.header ("Cache-Control", "no-cache, must-revalidate")	-- HTTP/1.1
	cgilua.header ("Pragma", "no-cache")							-- HTTP/1.0
	
	local funcname = cgi.rs
	
	if not export_list[funcname] then
		cgilua.put (string.format ("-:%s not callable", funcname))
	else
		local func = export_list[funcname]
		local rsargs = cgi["rsargs[]"]
		local result
		
		if not rsargs then
			result = func()
		elseif type (rsargs) == "string" then
			result = func (rsargs)
		elseif type (rsargs) == "table" then
			result = func (unpack (rsargs))
		else
			return
		end
		
		cgilua.put ("+:")
		cgilua.put (result)
	end
	
	return true
end

local function show_common_js ()
	cgilua.put [[
		// remote scripting library
		// (c) copyright 2005 modernmethod, inc
		var rs_debug_mode = false;
		
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
	]]
end

local function show_one (funcname)
	local uri = request_uri
	if string.find (uri, "?") then
		uri = uri .. "&rs=" .. cgilua.urlcode.escape (funcname)
	else
		uri = uri .. "?rs=" .. cgilua.urlcode.escape (funcname)
	end
	cgilua.put (string.format ([[
		// wrapper for %s
		
		function x_%s() {
			// count args; build URL
			var i, x, n;
			var url = "%s";
			var a = x_%s.arguments;
			for (i = 0; i < a.length-1; i++) 
				url = url + "&rsargs[]=" + escape(a[i]);
			url = url + "&rsrnd=" + new Date().getTime();
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
			rs_debug("x_%s url = " + url);
			rs_debug("x_%s waiting..");
			delete x;
		}
	]], funcname, funcname, uri, funcname, funcname, funcname))
end

function export (funcname, func)
	export_list[funcname] = func
end

local js_has_been_shown = false

function show_javascript ()
	if not js_has_been_shown then
		show_common_js ()
		js_has_been_shown = true
	end
	
	for fn,_ in pairs (export_list) do
		show_one (fn)
	end
end