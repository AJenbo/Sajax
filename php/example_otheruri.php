<?php
	function otherefucntion() {
		return "URI: ".$_SERVER['PHP_SELF'];
	}
	
	function otherefucntion2() {
		return otherefucntion();
	}
	
	require("sajax.php");
	sajax_export("otherefucntion", "otherefucntion2");
	sajax_handle_client_request();	
?>