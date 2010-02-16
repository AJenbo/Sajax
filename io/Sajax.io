//
// Sajax Io Backend
// (C) Copyright 2005 ModernMethod, Inc.
// Released under BSD license
//

String interpolate := method(
	work := self clone
	last_idx := 0
	while (1,
		idx := work find("<?io", last_idx)
		if (idx == Nil,
			// no more interps.. 
			break)
		idx := idx + 3
		end_idx := work find("?>", idx + 1)
		if (end_idx == Nil,
			// invalid interp expression.. we're done. 
			break)
		
 		// in the case of an empty interpolated expression, 
 		// lets move on
		if (end_idx - idx == 1,
			continue)
				
		// otherwise proceed with replacement. 
		slotName := work substring(idx+1, end_idx)
		find_str := "<?io" .. slotName .. "?>"
		// write("doString(" .. slotName .. ")")
		replace_str := sender doString(slotName)
		work := work replace(find_str, replace_str)
		last_idx = end_idx + 1
	)
	return work
)

Sajax := Object clone do (

	debug_mode := 0
	export_list := List clone
	js_has_been_shown := Nil
	
	init := method(
		return self
	)
	
	handle_client_request := method(
	
		if (my_getenv("QUERY_STRING") == Nil,
			return
		)
		
		form := CGI clone parse
		if (form hasKey("rs") == Nil,
			return
		)

		write("Content-type: text/html\n");
		write("Expires: Mon, 26 Jul 1997 05:00:00 GMT\n");   
		write("Cache-Control: no-cache, must-revalidate\n");
		write("Pragma: no-cache\n\n");
		
		func_name := form at("rs")	
		if (export_list contains(func_name) == Nil) then (
			write("-:$func_name not callable\n")
		) else (
			write("+:")
			args := form at("rsargs[]")
			result := sender performWithArgList(func_name, args)
			write(result)
		)
		exit;
	}
	
	show_common_js := method(
		
		if (debug_mode == 1,
			debugModeTrueFalse := "true",
			debugModeTrueFalse := "false")
		
		html := """

		// remote scripting library
		// (c) copyright 2005 modernmethod, inc
		var rs_debug_mode = <?io debugModeTrueFalse ?>;
		
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
		
		"""
		write(html interpolate)
	}
	
	esc := method(val,
		return val replace("\"", "\\\\\"");
	)
	
	my_getenv := method(val,
		my_get := Nil
		if (?System) then (
			my_get := System getSlot("getenv")
		) else (
			my_get := Object getSlot("getenv")
		)
		return my_get(val)
	)
	
	get_my_uri := method(
		uri := "http://www.example.com/"
		if (my_getenv("REQUEST_URI") != Nil) then (
			uri := my_getenv("REQUEST_URI")
		) else (
			if (my_getenv("SCRIPT_NAME") != Nil) then (
				uri := my_getenv("SCRIPT_NAME")
				if (my_getenv("QUERY_STRING") != Nil) then (
					uri := uri .. "?" .. my_getenv("QUERY_STRING")
				)
			)
		)
		return uri
	)

	show_one := method(func_name,
		uri := get_my_uri
		func_name_encoded := CGI encodeUrlParam(func_name)
		if (uri contains("?") == Nil) then (
        	uri = uri .. "?rs=" .. func_name_encoded
        ) else (
			uri = uri .. "&rs=" .. func_name_encoded
		)
			
		html := """
		// wrapper for <?io func_name ?>
		
		function x_<?io func_name ?>() {
			// count args; build URL
			var i, x, n;
			var url = "<?io esc(uri) ?>";
			var a = x_<?io func_name ?>.arguments;
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
			rs_debug("x_<?io func_name ?> url = " + url);
			rs_debug("x_<?io func_name ?> waiting..");
			delete x;
		}
		"""
		write(html interpolate)
	)

	export := method(func,
		export_list add(func)
	}
	
	show_javascript := method(
		if (js_has_been_shown == Nil,
			show_common_js
		)
		export_list foreach(index, func_name,
			show_one(func_name)
		)
	)
	
)

