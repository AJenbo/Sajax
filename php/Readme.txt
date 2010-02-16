SAJAX PHP BACKEND
-----------------

Contributed and copyighted by Thomas Lackner (http://www.modernmethod.com/) and Anders Jenbo.

If you are using PHP < 5.2 you will need to a substitue for JSON_encode and JSON_decode, you might want to try this out http://abeautifulsite.net/notebook/71

Usage:
sajax_export() takes a list of functions that are alowed to be called from javascript, there are 4 parameters for each function you send to sajax_export. If strings are used in stead of arrays the functions will just have all options set to there default (like in older versions of Sajax).

name = the name of the function (requred)
method = GET : POST (default is GET)
asynchronous = true : false (default is true, if set to false the script will pause untill the call has been compleated)
uri = the uri where the function lives.

The default method can be changed by setting $sajax_request_type to "POST" (this can also be done at runtime).
The default uri can be changed by setting $sajax_remote_uri (this can also be done at runtime).

Sample:
sajax_export(
	"myFunction",
	array("name" => "myPostFunction", "method" => "POST"),
	array("name" => "sleep", "asynchronous" => true),
	array("name" => "myOffSitefunction", "uri" => "http://myotheresite.com/ajax.php")
);

sajax_export() may be called multiple times.

To get debug alert's set $sajax_debug_mode to true.

You can redirect the users browser to a frindly error page incase his/her borwser issn't supported by sajax by setting $sajax_failure_redirect to an URI.

Calling sajax_get_common_js() will print the client side script.