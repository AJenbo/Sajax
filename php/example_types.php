<?
	require("Sajax.php");

	function return_array() {
		return array("name" => "Tom", "age" => 26);
	}
	
	function return_object() {
		class MyObj {
			var $name, $age;
			
			function MyObj($name, $age) {
				$this->name = $name;
				$this->age = $age;
			}
		}
		$o = new MyObj("Tom", 26);
		return $o;
	}
	
	function return_string() {
		return "Name: Tom / Age: 26";
	}
	
	function return_int() {
		return 26;
	}
	
	function return_float() {
		return 26.25;
	}
	
	$sajax_request_type = "GET";
	sajax_init();
	sajax_export("return_array", "return_object", "return_string",
		"return_int", "return_float");
	sajax_handle_client_request();	
?>
<html>
<head>
<script>
<?
	sajax_show_javascript();
?>
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
</script>
<body>
<button onclick="x_return_array(display_result);">Return as array (will become an object)</button>
<button onclick="x_return_object(display_result);">Return as object</button>
<button onclick="x_return_string(display_result);">Return as string</button>
<button onclick="x_return_int(display_result);">Return as int</button>
<button onclick="x_return_float(display_result);">Return as float/double</button>
</body>
</html>

