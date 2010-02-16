<?php	
if (!isset($SAJAX_INCLUDED)) {

	$sajax_debug_mode = 0;
	$sajax_export_list = array();
	
	function sajax_init() {
	}
	
	function sajax_handle_client_request() {
		global $sajax_export_list;
		
		if (empty($_GET["rs"])) 
			return;

		// Bust cache in the head
		header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
		header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		// always modified
		header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
		header ("Pragma: no-cache");                          // HTTP/1.0
			
		$func_name = $_GET["rs"];
		if (! in_array($func_name, $sajax_export_list))
			echo "-:$func_name not callable";
		else {
			echo "+:";
			if (empty($_GET["rsargs"])) 
				$result = call_user_func($_GET["rs"]);
			else
				$result = call_user_func_array($_GET["rs"], $_GET["rsargs"]);
			echo $result;
		}
		exit;
	}
	
	function sajax_get_common_js() {
		global $sajax_debug_mode;
		
		ob_start();
		?>
		
		// remote scripting library
		// (c) copyright 2005 modernmethod, inc
		var sajax_debug_mode = <?php echo $sajax_debug_mode ? "true" : "false"; ?>;
		
		function sajax_debug(text) {
			if (sajax_debug_mode)
				alert("RSD: " + text)
		}
 		function sajax_init_object() {
 			sajax_debug("sajax_init_object() called..")
 			
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
				sajax_debug("Could not create connection object.");
			return A;
		}
		function sajax_do_call(func_name, url, args) {
			var i, x, n;
			for (i = 0; i < args.length-1; i++) 
				url = url + "&rsargs[]=" + escape(args[i]);
			url = url + "&rsrnd=" + new Date().getTime();
			x = sajax_init_object();
			x.open("GET", url, true);
			x.onreadystatechange = function() {
				if (x.readyState != 4) 
					return;
				sajax_debug("received " + x.responseText);
				
				var status;
				var data;
				status = x.responseText.charAt(0);
				data = x.responseText.substring(2);
				if (status == "-") 
					alert("Error: " + data);
				else  
					args[args.length-1](data);
			}
			x.send(null);
			sajax_debug(func_name + " url = " + url);
			sajax_debug(func_name + " waiting..");
			delete x;
		}
		
		<?php
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}
	
	function sajax_show_common_js() {
		echo sajax_get_common_js();
	}
	
	// javascript escape a value
	function sajax_esc($val)
	{
		return str_replace('"', '\\\\"', $val);
	}

	function sajax_get_one_stub($func_name) {
		global $REQUEST_URI;
		
		$uri = $REQUEST_URI;
		if (strpos($uri,"?") === false) 
			$uri .= "?rs=".urlencode($func_name);
		else
			$uri .= "&rs=".urlencode($func_name);
		
		ob_start();	
		?>
		
		// wrapper for <?php echo $func_name; ?>
		
		function x_<?php echo $func_name; ?>() {
			// count args; build URL
			
			sajax_do_call("<?php echo $func_name; ?>",
				"<?php echo sajax_esc($uri); ?>",
				x_<?php echo $func_name; ?>.arguments);
		}
		
		<?php
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}
	
	function sajax_show_one_stub($func_name) {
		echo sajax_get_one_stub($func_name);
	}
	
	function sajax_export() {
		global $sajax_export_list;
		
		$n = func_num_args();
		for ($i = 0; $i < $n; $i++) {
			$sajax_export_list[] = func_get_arg($i);
		}
	}
	
	$sajax_js_has_been_shown = 0;
	function sajax_get_javascript()
	{
		global $sajax_js_has_been_shown;
		global $sajax_export_list;
		
		$html = "";
		if (! $sajax_js_has_been_shown) {
			$html .= sajax_get_common_js();
			$sajax_js_has_been_shown = 1;
		}
		foreach ($sajax_export_list as $func) {
			$html .= sajax_get_one_stub($func);
		}
		return $html;
	}
	
	function sajax_show_javascript()
	{
		echo sajax_get_javascript();
	}
	
	$SAJAX_INCLUDED = 1;
}
?>
