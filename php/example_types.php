<?php
date_default_timezone_set('Europe/Copenhagen');

$bday = strtotime('5 September 1983');
$age = (date('z') - date('z', $bday)) / 1000 + date('Y') - date('Y', $bday);

function return_array()
{
    global $age;
    return ["name" => "Anders", "age" => $age];
}

function return_object()
{
    global $age;
    class MyObj
    {
        public $name;
        public $age;

        function __construct($name, $age)
        {
            $this->name = $name;
            $this->age = $age;
        }
    }
    return new MyObj("Anders", $age);
}

function return_int()
{
    global $age;
    return floor($age);
}

function return_float()
{
    global $age;
    return $age;
}

function return_string()
{
    global $age;
    return "Anders is " . floor($age) . " years old.";
}

require_once 'Sajax.php';
Sajax\Sajax::$requestType = 'GET';
//Sajax\Sajax::$debugMode = true;
Sajax\Sajax::$failureRedirect = '/sajaxfail.html';
Sajax\Sajax::export(
    [
        'return_array'  => [],
        'return_object' => [],
        'return_int'    => [],
        'return_float'  => [],
        'return_string' => [],
    ]
);
Sajax\Sajax::handleClientRequest();

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Sajax PHP return types example</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script type="text/javascript" src="json2.stringify.js"></script>
<script type="text/javascript" src="json_stringify.js"></script>
<script type="text/javascript" src="json_parse_state.js"></script>
<script type="text/javascript" src="sajax.js"></script>
<script type="text/javascript"><!--
<?php Sajax\Sajax::showJavascript(); ?>
function display_result(val) {
    var repr;

    repr = "";
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
<button onclick="x_return_array(display_result);">Return as array (will become an object)</button>
<button onclick="x_return_object(display_result);">Return as object</button>
<button onclick="x_return_int(display_result);">Return as int</button>
<button onclick="x_return_float(display_result);">Return as float/double</button>
<button onclick="x_return_string(display_result);">Return as string</button>
</body>
</html>
