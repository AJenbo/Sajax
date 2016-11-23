<?php
/**
 * Simple multiply function
 *
 * @param float $firstNumber   Number to multiply
 * @param float $secoundNumber Number to multiply with
 *
 * @return return float
 */
function multiply(float $firstNumber, float $secoundNumber): float
{
    return $firstNumber * $secoundNumber;
}

// Include the libery
require_once 'Sajax.php';

// Uncomment the following line to turn on debugging
//Sajax\Sajax::$debugMode = true;

// Set redirect page in case of error (only works when not debugging)
Sajax\Sajax::$failureRedirect = '/sajaxfail.html';

// Export the multiply with default options (empty array as value)
Sajax\Sajax::export(['multiply' => []]);

// Handel the ajax request, script will exit here on ajax calls
Sajax\Sajax::handleClientRequest();

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Sajax Multiplier example</title>
<script type="text/javascript" src="json2.stringify.js"></script>
<script type="text/javascript" src="json_stringify.js"></script>
<script type="text/javascript" src="json_parse_state.js"></script>
<script type="text/javascript" src="sajax.js"></script>
<script type="text/javascript"><!--
<?php Sajax\Sajax::showJavascript(); ?>

/**
 * Dispatch a call to the PHP function
 */
function do_multiply() {
    // get the folder name
    var x, y;

    x = parseFloat(document.getElementById("x").value);
    y = parseFloat(document.getElementById("y").value);
    x_multiply(x, y, do_multiply_cb);
}

/**
 * Handel the return form the PHP function on return
 */
function do_multiply_cb(z) {
    document.getElementById("z").value = z;
}
//-->
</script>
</head>
<body>
<input type="text" name="x" id="x" value="2" size="3" />
*
<input type="text" name="y" id="y" value="3" size="3" />
=
<input type="text" name="z" id="z" value="" size="3" />
<input type="button" name="check" value="Calculate" onclick="do_multiply(); return false;" />
</body>
</html>
