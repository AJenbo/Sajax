<?php
// Set default time zone
date_default_timezone_set('Europe/Copenhagen');

/**
 * Get some request information
 *
 * @return string
 */
function test(): string
{
    $string = 'URI: '.$_SERVER['PHP_SELF'];
    $string .= "\n\n" . '-- GET --' . "\n";
    if (!empty($_GET['rsargs'])) {
        $string .= $_GET['rsargs'];
    }

    $string .= "\n" . '-- POST --' . "\n";
    if (!empty($_POST['rsargs'])) {
        $string .= $_POST['rsargs'];
    }

    return $string;
}

/**
 * Just a different name for test to make it easy to export under different methodes
 *
 * @return string
 */
function testGet(): string
{
    return test();
}

/**
 * Just a different name for test to make it easy to export under different methodes
 *
 * @return string
 */
function testPost(): string
{
    return test();
}

/**
 * Get the current date
 *
 * @return string
 */
function getTheTime(): string
{
    return date('Y-m-d h:i:s');
}


// Include the libery
require_once 'Sajax.php';

// Uncomment the following line to turn on debugging
//Sajax\Sajax::$debugMode = true;

// Set redirect page in case of error (only works when not debugging)
Sajax\Sajax::$failureRedirect = '/sajaxfail.html';

// Export methodes with options
Sajax\Sajax::export(
    [
        'testGet'       => ['method' => 'GET'],
        'testPost'      => ['method' => 'POST'],
        'getTheTime'    => ['method' => 'GET'],
        'test'          => ['method' => 'GET'],
        'sleep'         => ['asynchronous' => false],
        'otherFucntion' => ['uri' => 'example_otheruri.php'],
    ]
);

// Handel the ajax request, script will exit here on ajax calls
Sajax\Sajax::handleClientRequest();

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Example of Sajax Options</title>
<script type="text/javascript" src="json2.stringify.js"></script>
<script type="text/javascript" src="json_stringify.js"></script>
<script type="text/javascript" src="json_parse_state.js"></script>
<script type="text/javascript" src="sajax.js"></script>
<script type="text/javascript"><!--
<?php Sajax\Sajax::showJavascript(); ?>
/**
 * Handel the return form the PHP function on return
 */
function print_result(v) {
    alert(v);
}
//-->
</script>
</head>
<body>
<!-- Testing if the browser supports GET -->
<button onclick="x_testGet(1, 2, 3, print_result);">Test GET</button>

<!-- Testing if the browser supports POST -->
<button onclick="x_testPost(1, 2, 3, print_result);">Test POST</button>

<!-- Forcing the function to POST -->
<button onclick="sajaxRequest_type ='POST'; x_test(1, 2, 3, print_result); sajax_request_type ='';">Test force POST</button>

<!-- if sajax_target_id is set, the sesponce will be inserted as HTML in an element with sajax_target_id for id-->
<button onclick="sajaxTargetId = 'time'; x_getTheTime(); sajax_target_id = '';">Test updating IDs</button>

<!-- Calling a synchronous will cause the script to wait for the responce -->
<button onclick="x_sleep(3, function(){}); alert('Link was clicked!');">Test synchronous</button>

<!-- Different URI set at config -->
<button onclick="x_otherFucntion(print_result);">Call to other uri.</button>

<div id="time"><em>Time will appear here</em></div>
</body>
</html>
