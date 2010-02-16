<cfinclude template="sajax.cfm">
<cfscript>
	bday = "9/5/1983";
	age = DateFormat(now(), "yyyy")-DateFormat(bday, "yyyy")-(DateFormat(bday, "m.dd")-DateFormat(now(), "m.dd"))/10;
	
	function return_struct() {
		struct = structNew();
		struct["name"] = "Anders";
		struct["age"] = age;
		return struct;
	}
	
	function return_list() {
		list = "";
		list = listappend(list, "Anders");
		list = listappend(list, age);
		return list;
	}
	
	function return_float() {
		return age;
	}
	
	function return_int() {
		return int(age);
	}
	
	function return_string() {
		return "Anders is #int(age)# years old.";
	}
	
	sajax_request_type = "GET";
//	sajax_debug_mode = true;
	sajax_export("return_struct", "return_list", "return_int", "return_float", "return_string");
	sajax_handle_client_request();
</cfscript>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Sajax ColdFusion return types example</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script type="text/javascript" src="json2.stringify.js"></script>
<script type="text/javascript" src="json_stringify.js"></script>
<script type="text/javascript" src="json_parse_state.js"></script>
<script type="text/javascript" src="sajax.js"></script>
<script type="text/javascript"><!--
	<cfscript>sajax_show_javascript();</cfscript>
	function display_result(val) {
		var repr;
		
		repr  = "";
		repr += "Type: " + typeof val + "\n";
		repr += "Value: ";
		if (typeof val == "object" ||
			typeof val == "array") {
			repr += "{ ";
			for (var i in val) 
				repr += i + ": " + val[i] + ", ";
			repr = repr.substr(0, repr.length-2) + " }";
		} else {
			repr += val;
		}
		alert(repr);
	}
//-->
</script>
</head>
<body>
<button onclick="x_return_struct(display_result);">Return as struct (will become an object)</button>
<button onclick="x_return_list(display_result);">Return as list (will become a string)</button>
<button onclick="x_return_int(display_result);">Return as int</button>
<button onclick="x_return_float(display_result);">Return as float/double</button>
<button onclick="x_return_string(display_result);">Return as string</button>
</body>
</html>
