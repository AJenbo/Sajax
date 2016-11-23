<?php
// Set default time zone
date_default_timezone_set('Europe/Copenhagen');

// Find bday
$bday = strtotime('5 September 1983');

// Caculate the difference form bday and now
$age = (date('z') - date('z', $bday)) / 1000 + date('Y') - date('Y', $bday);

/**
 * Return an array
 *
 * @return array
 */
function returnArray(): array
{
    global $age;
    return ["name" => "Anders", "age" => $age];
}

/**
 * Return an object
 *
 * @return MyObj
 */
function returnObject()
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

/**
 * Return an integer representation
 *
 * @return int
 */
function returnInt(): int
{
    global $age;
    return floor($age);
}

/**
 * Return a float representation
 *
 * @return float
 */
function returnFloat(): float
{
    global $age;
    return $age;
}

/**
 * Return a string representation
 *
 * @return string
 */
function returnString(): string
{
    global $age;
    return "Anders is " . floor($age) . " years old.";
}


// Include the libery
require_once 'Sajax.php';

// Set default request methode to GET (only other option is POST)
Sajax\Sajax::$requestType = 'GET';

// Uncomment the following line to turn on debugging
//Sajax\Sajax::$debugMode = true;

// Set redirect page in case of error (only works when not debugging)
Sajax\Sajax::$failureRedirect = '/sajaxfail.html';

// Export methodes with default options (empty array as value)
Sajax\Sajax::export(
    [
        'returnArray'  => [],
        'returnObject' => [],
        'returnInt'    => [],
        'returnFloat'  => [],
        'returnString' => [],
    ]
);

// Handel the ajax request, script will exit here on ajax calls
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

/**
 * Handel the return form the PHP function on return
 */
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
<button onclick="x_returnArray(display_result);">Return as array (will become an object)</button>
<button onclick="x_returnObject(display_result);">Return as object</button>
<button onclick="x_returnInt(display_result);">Return as int</button>
<button onclick="x_returnFloat(display_result);">Return as float/double</button>
<button onclick="x_returnString(display_result);">Return as string</button>
</body>
</html>
