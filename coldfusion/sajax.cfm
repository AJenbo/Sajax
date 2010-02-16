<!---
 Cold Fusion port of Sajax
 written by Eric Moritz <eric.moritz@gmail.com>
 This port was made to emulate the php sajax api, function calls should translate the same. If you have any trouble, please refer to the sample application.

--->
<cfsilent><!--- Send the no cache haeders if this is an ajax request --->
<cfif isdefined("url.rs")>
  <cfheader name="Expires" value="Mon, 26 Jul 1997 05:00:00 GMT"> 
  <cfheader name="Last-Modified" value="#gethttptimestring(now())#">
  <cfheader name="Cache-Control" value="no-cache, must-revalidate">
  <cfheader name="Pragma" value="no-cache">
</cfif>

<!--- emulate the php exit keyword  --->
<cffunction name="exit">
	<cfabort>
</cffunction>

<cffunction name="sajax_get_one_stub">
	 <cfargument name="func_name">
		
		<cfscript>
		uri = cgi.query_string;
		if (find(uri,"?") gt 0) 
			uri = uri & "?rs=" & urlencodedformat(func_name);
		else
			uri = uri & "&rs=" & urlencodedformat(func_name);
		uri = cgi.script_name & uri;
		</cfscript>
			
		<cfoutput>		
		// wrapper for #func_name#
		
		function x_#func_name#() {
			// count args; build URL
			
			sajax_do_call("#func_name#",
				"#sajax_esc(uri)#",
				x_#func_name#.arguments);
		}
	 </cfoutput>	
</cffunction>
<cffunction name="sajax_get_common_js">
<cfoutput>
		// remote scripting library
		// (c) copyright 2005 modernmethod, inc
		var sajax_debug_mode =  #iif(sajax_debug_mode,'"true"','"false"')#;
		
		function sajax_debug(text) {
			if (sajax_debug_mode)
				alert("RSD: " + text)
		}
 		function sajax_init_object() {
 			sajax_debug("sajax_init_object() called..")
 			
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
				sajax_debug("Could not create connection object.");
			return A;
		}
		function sajax_do_call(func_name, url, args) {
			var i, x, n,responseText;
			url = url + "&rsargs="; 
			for (i = 0; i < args.length-1; i++) 
			url =	url + escape(args[i]) + escape("#JSStringFormat(sajax_url_array_delim)#");
			url = url + "&rsrnd=" + new Date().getTime();
			x = sajax_init_object();
			x.open("GET", url, true);
			x.onreadystatechange = function() {
				if (x.readyState != 4) 
					return;
				responseText = x.responseText.replace(/^\s+/,''); // remove leading space, cf loves to put extra whitespace.
				sajax_debug("received " + responseText);
				
				var status;
				var data;
				status = responseText.charAt(0);
				data = responseText.substring(1);
				if (status == "-") 
					alert("Error: " + data);
				else  
					args[args.length-1](data);
			}
			x.send(null);
			sajax_debug(func_name + " url = " + url);
			sajax_debug(func_name + " waiting..");
			delete x;
		}
</cfoutput>
</cffunction>
<cffunction name="sajax_handle_client_request">

<cfscript>
	var func_name="";
	var result=0;
</cfscript>
<cftry>
<cfsavecontent variable="output">
<cfscript>


	if(not isdefined("url.rs"))
		return;
	
		func_name = url['rs'];
		if(not ListFindNoCase(sajax_export_list, func_name))
			writeoutput("-:#func_name# not callable");
		else {
			writeoutput("+");
			if(not isdefined("url.rsargs"))
				result = call_user_func(url['rs']);
			else
				result = call_user_func_array(url['rs'], get_url_array(url['rsargs']));
			if(isdefined("result"))
			  writeoutput(result);
		}
		if(sajax_test_mode eq 0)
		  exit();		
</cfscript>
</cfsavecontent>
<cfoutput>#output#</cfoutput>
<cfcatch>
<cfoutput>-#cfcatch.type#
 #cfcatch.message#
 #cfcatch.detail#
 <cfif arraylen(cfcatch.tagcontext) gt 0>
 in #cfcatch.tagcontext[1].template# at #cfcatch.tagcontext[1].line#
 </cfif></cfoutput>
 <cfabort>
 </cfcatch>
 </cftry>
 </cffunction>

 <cfscript>
 sajax_debug_mode = 0;
sajax_test_mode = 0;
sajax_export_list = "";
sajax_url_array_delim = chr(31);
function call_user_func(user_func) {
	return(call_user_func_array(user_func,arraynew(1)));
}
function sajax_export() {
	var keys = structkeylist(arguments);
	var size = listlen(keys);
	var key = "";
	
	for(i =1; i lte size;i=i+1) {
		key = listgetat(keys,i);
		sajax_export_list = listappend(sajax_export_list,arguments[key]);
	}
}
function call_user_func_array(user_func, arg_array) {
	var func_call = "";
	var func_args = "";
	var size = ArrayLen(arg_array);

	/* Loop though each of the args  */
	for(i=1; i lte size; i = i + 1) {
		func_args = func_args & "arg_array[#i#]";
		if(i lt size)
			func_args = func_args & ",";
	}

	return(evaluate("#user_func#(#func_args#)"));
}
function get_url_array(url_array) {
	return(ListToArray(url_array,sajax_url_array_delim));
}

function set_url_array(array) {
	return(ArrayToList(array,sajax_url_array_delim));
}

function sajax_init() {
}




	function sajax_show_common_js() {
		sajax_get_common_js();
	}
	
	// javascript escape a value
	function sajax_esc(val)
	{
		return(jsstringformat(val));
	}

	
	function sajax_show_one_stub(func_name) {
		sajax_get_one_stub(func_name);
	}
	
	sajax_js_has_been_shown = 0;
	function sajax_get_javascript()
	{
		if (not sajax_js_has_been_shown) {
			sajax_get_common_js();
			sajax_js_has_been_shown = 1;
		}
		
		size = listlen(sajax_export_list);
		for(i = 1; i lte size; i=i+1) {
			func = listgetat(sajax_export_list,i);
			sajax_get_one_stub(func);
		}
	}
	
	function sajax_show_javascript()
	{
		sajax_get_javascript();
	}
	
	SAJAX_INCLUDED = 1;

</cfscript>
</cfsilent>
