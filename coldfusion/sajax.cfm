<cfsilent>

<cfset sajax_version = "0.13">
<cfset sajax_debug_mode = false>
<cfset sajax_export_array = arraynew(1)>
<cfset sajax_export_list = "">
<cfset sajax_method_list = "">
<cfset sajax_asynchronous_list = "">
<cfset sajax_uri_list = "">
<cfset sajax_remote_uri = "">
<cfset sajax_failure_redirect = "">
<cfset sajax_request_type = "GET">
<cfset sajax_request_asynchronous = "true">

<!--- Always call server if this is a Sajax GET call --->
<cfif isdefined("URL.rs")>
	<cfheader name="Cache-Control" value="max-age=0, must-revalidate">
	<cfheader name="Pragma" value="no-cache">
</cfif>

<!--- emulate the php exit keyword --->
<cffunction name="exit">
	<cfabort>
</cffunction>

<cffunction name="sajax_handle_client_request">
	<cftry>
		<cfsavecontent variable="output">
			<cfscript>
				if(not isdefined("URL.rs") and not isdefined("FORM.rs"))
					return;
				if(isdefined("URL.rs")) {
					func_name = URL.rs;
					if(isdefined("URL.rsargs"))
						args = URL.rsargs;
				} else {
					func_name = FORM.rs;
					if(isdefined("FORM.rsargs"))
						args = FORM.rsargs;
				}
				
				if(isdefined("args")) {
					args = DeserializeJSON(args);
				} else {
					args = arraynew(1);
				}
				if(not ListFindNoCase(sajax_export_list, func_name)) {
					error = "#func_name# not callable";
				} else {
					result = SerializeJSON(call_user_func_array(func_name, args));
				}
			</cfscript>
		</cfsavecontent>
		<cfcatch>
			<cfheader name="Content-Type" value="text/plain; charset=UTF-8">
			<cfoutput>-:#cfcatch.type#
#cfcatch.message#
#cfcatch.detail#
<cfif arraylen(cfcatch.tagcontext) gt 0>
in #cfcatch.tagcontext[1].template# at #cfcatch.tagcontext[1].line#
</cfif>
</cfoutput>
			<cfabort>
		</cfcatch>
	</cftry>
	<cfheader name="Content-Type" value="text/plain; charset=UTF-8">
	<cfscript>
		//Remove start and end white space from output
		output = REReplace(output,"^\s*|\s*$","","ALL");
		if(isdefined("result") and "#output#" eq "")
			writeoutput("+:#result#");
		else if("#output#" eq "")
			writeoutput("-:#error#");
		else
			writeoutput("-:#output#");
		exit();
	</cfscript>
</cffunction>

<cfscript>
	sajax_js_has_been_shown = false;
	function sajax_show_javascript()
	{

		if (not sajax_js_has_been_shown) {
			
			writeoutput("
	sajax_debug_mode = #iif(sajax_debug_mode, 'true', 'false')#;
	sajax_failure_redirect = ""#sajax_failure_redirect#"";
");
			//TODO get from an array instead of list
			size = listlen(sajax_export_list);
			for(i = 1; i lte size; i=i+1) {
				name = listgetat(sajax_export_list, i);
				method = listgetat(sajax_method_list, i);
				asynchronous = listgetat(sajax_asynchronous_list, i);
				uri = listgetat(sajax_uri_list, i);
				if(uri == "##")
					uri = "";
				writeoutput("
	function x_#name#() {
		return sajax_do_call(""#name#"", arguments, ""#method#"", #asynchronous#, ""#uri#"");
	}");
			}
			sajax_js_has_been_shown = true;
		}
	}
	
	function sajax_export() {
		//TODO make it a multi dimentional array of options
		//TODO prevent multiple instances of the same functions
		//if(not isarray(function))
		var keys = structkeylist(arguments);
		var size = listlen(keys);
		var key = "";
		
		for(i=1; i lte size;i=i+1) {
			key = listgetat(keys, i);
			sajax_export_list = listappend(sajax_export_list, arguments[key]);
			sajax_method_list = listappend(sajax_method_list, sajax_request_type);
			sajax_asynchronous_list = listappend(sajax_asynchronous_list, sajax_request_asynchronous);
			if(sajax_remote_uri == "")
				sajax_remote_uri = "##";
			sajax_uri_list = listappend(sajax_uri_list, sajax_remote_uri);
			if(sajax_remote_uri == "##")
				sajax_remote_uri = "";
		}
	}
	
	function call_user_func_array(user_func, arg_array) {
		//TODO test with multi dimentional arrays
		var func_call = "";
		var func_args = "";
		var size = ArrayLen(arg_array);
	
		/* Loop though each of the args */
		for(i=1; i lte size; i = i + 1) {
			func_args = func_args & "arg_array[#i#]";
			if(i lt size)
				func_args = func_args & ",";
		}
	
		return evaluate("#user_func#(#func_args#)");
	}
	
	SAJAX_INCLUDED = 1;
</cfscript>
</cfsilent>
