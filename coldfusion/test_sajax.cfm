<cfset url.rs = "test">
<cfinclude template="sajax.cfm">
<cfscript>
	function assert(v1,v2){
		writeoutput("#v1# = #v2#<br/>");
	}
	function test() {
		return(42);
	}
	function test2(v) {
		return(v);
	}
	function return_nothing() {
        }
	function make_error() {
		a = 1 + "a"; // cause an error	
	}	
</cfscript>
<!--- test the call user function --->
<cfscript>
	assert(call_user_func("test"),42);
	foo = arraynew(1);
	foo[1] = 42;
	assert(call_user_func_array("test2",foo),foo[1]);
</cfscript>
<!--- Test the url array fuctions --->
<cfscript>
	foo = arraynew(1);
	foo[1] = 1;
	foo[2] = 2;

	a = set_url_array(foo);
	a = get_url_array(a);
	for(i = 1; i lte arraylen(foo);i=i+1) {
		assert(foo[i],a[i]);
	}
</cfscript>
<!--- Test the export function --->
<cfscript>
	sajax_export("test","test2","return_nothing","make_error");
	assert(sajax_export_list,"test,test2,return_nothing,make_error");
</cfscript>

<!--- Test the request handler --->
<cfscript>
	sajax_test_mode = 1;
	url.rs = "test";
	sajax_handle_client_request();

	foo = arraynew(1);
	foo[1] = 43;

	writeoutput("<br/>");
	url.rs = "test2";
	url.rsargs = set_url_array(foo);
	sajax_handle_client_request();

	writeoutput("<br/>");
	url.rs = "return_nothing";
	sajax_handle_client_request();

	writeoutput("<br/>");
	url.rs = "make_error";
	sajax_handle_client_request();
</cfscript>
