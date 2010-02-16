<?	
if (!isset($INCL_REMOTE_SCRIPTING)) {

	$rs_debug_mode = 0;
	$rs_export_list = array();
	
	function rs_init() {
	}
	
	function rs_handle_client_request() {
		global $rs_export_list;
		
		if (empty($_GET["rs"])) 
			return;

		// Bust cache in the head
		header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
		header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		// always modified
		header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
		header ("Pragma: no-cache");                          // HTTP/1.0
			
		$func_name = $_GET["rs"];
		if (! in_array($func_name, $rs_export_list))
			echo "-:$func_name not callable";
		else {
			echo "+:";
			$result = call_user_func_array($_GET["rs"], $_GET["rsargs"]);
			echo $result;
		}
		exit;
	}
	
	function rs_show_common_js() {
		global $rs_debug_mode;
		
		?>
		
		// remote scripting library
		// (c) copyright 2005 modernmethod, inc
		var rs_debug_mode = <?= $rs_debug_mode ? "true" : "false"; ?>;
		
		function rs_debug(text) {
			if (rs_debug_mode)
				alert("RSD: " + text)
		}
 		function rs_init_object() {
 			rs_debug("rs_init_object() called..")
 			
 			var A;
			try {
				A=new ActiveXObject("Msxml2.XMLHTTP");
			} catch (e) {
				try {
					A=new ActiveXObject("Microsoft.XMLHTTP");
				} catch (oc) {
					A=null;
				}
			}
			if(!A && typeof XMLHttpRequest != "undefined")
				A = new XMLHttpRequest();
			if (!A)
				rs_debug("Could not create connection object.");
			return A;
		}
		<?
	}
	
	// javascript escape a value
	function rs_esc($val)
	{
		return str_replace('"', '\\\\"', $val);
	}
	
	function rs_show_one($func_name) {
		global $REQUEST_URI;
		
		$uri = $REQUEST_URI;
		if (strpos($uri,"?") === false) 
			$uri .= "?rs=".urlencode($func_name);
		else
			$uri .= "&rs=".urlencode($func_name);
			
		?>
		
		// wrapper for <?= $func_name; ?>
		
		function x_<?= $func_name; ?>() {
			// count args; build URL
			var i, x, n;
			var url = "<?= rs_esc($uri); ?>", a = x_<?= $func_name; ?>.arguments;
			for (i = 0; i < a.length-1; i++) 
				url = url + "&rsargs[]=" + escape(a[i]);
			url = url + "&rsrnd=" + new Date().getTime();
			x = rs_init_object();
			x.open("GET", url, true);
			x.onreadystatechange = function() {
				if (x.readyState != 4) 
					return;
				rs_debug("received " + x.responseText);
				
				var status;
				var data;
				status = x.responseText.charAt(0);
				data = x.responseText.substring(2);
				if (status == "-") 
					alert("Error: " + callback_n);
				else  
					a[a.length-1](data);
			}
			x.send(null);
			rs_debug("x_<?= $func_name; ?> url = " + url);
			rs_debug("x_<?= $func_name; ?> waiting..");
			delete x;
		}
		
		<?
	}
	
	function rs_export() {
		global $rs_export_list;
		
		$n = func_num_args();
		for ($i = 0; $i < $n; $i++) {
			$rs_export_list[] = func_get_arg($i);
		}
	}
	
	$rs_js_has_been_shown = 0;
	function rs_show_javascript()
	{	
		global $rs_js_has_been_shown;
		global $rs_export_list;
		
		if (! $rs_js_has_been_shown) {
			rs_show_common_js();
			$rs_js_has_been_shown = 1;
		}
		foreach ($rs_export_list as $func) {
			rs_show_one($func);
		}
	}
	
	$INCL_REMOTE_SCRIPTING = 1;
}
?>
