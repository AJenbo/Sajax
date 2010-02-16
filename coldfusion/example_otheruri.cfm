<cfinclude template="sajax.cfm">
<cfscript>
	function otherefucntion() {
		return "URI: example_otheruri.cfm";
	}
	
	function otherefucntion2() {
		return otherefucntion();
	}
	
	//allowed functions
	sajax_export("otherefucntion", "otherefucntion2");
	sajax_handle_client_request();
	
</cfscript>