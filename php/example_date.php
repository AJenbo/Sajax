<?
require("Sajax.php");

// Leonardo Lorieri
// My first SAJAX implementention, few lines of inspiration
// A good way to understand SAJAX programming
//
// Work Flow:
// 1- starting by the <body onload="get_date()">
// 2- loading the server's date from the php function,
//      calling the javascript function to show it.
// 3- scheduling another load to the next second
//
// Disclaimer: Hey! I dont speak english
// Under (put your choice here) license

function show_now() {
	//return server date
	return date("l dS of F Y h:i:s A");
}

//starting SAJAX stuff
$sajax_request_type = "GET";
sajax_init();
sajax_export("show_now");
sajax_handle_client_request();
?>
<html>
<head>
        <title>Show Server's Date</title>
        <script>
        <?
        sajax_show_javascript();
        ?>
        function show_me(date_server) {
                document.getElementById("date_div").innerHTML = date_server;
        }

        function get_date() {

                //put the return of php's show_now func
                //to the javascript show_me func as a parameter
                x_show_now(show_me);

                //do it every 1 second
                setTimeout("get_date()", 1000);
        }
        </script>

</head>
<body  onload="get_date();">
Server date: <div id="date_div">(loading...)</div>
</body>
</html>
