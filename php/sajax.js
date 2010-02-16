// remote scripting library
// (c) copyright 2005 modernmethod, inc
var sajax_debug_mode = false;
var sajax_failure_redirect = "";
var sajax_remote_uri = "";
var sajax_request_type = "";
var sajax_target_id = "";

function sajax_debug(text) {
	if (sajax_debug_mode) {
		alert(text);
	}
	return true;
}

function sajax_failure(text) {
	if(sajax_failure_redirect != "" && !sajax_debug_mode) {
		window.location.href = sajax_failure_redirect;
	} else {
		sajax_debug(text);
	}
	return false;
}

var sajax_requests = [];

function sajax_cancel(id) {
	 if(arguments.length === 0) {
		for (var i = 0; i < sajax_requests.length; i++) {
			if(sajax_requests[i]) {
				sajax_requests[i].abort();
				sajax_requests.splice(i, 1, null);
			}
		}
	} else if(sajax_requests[id]) {
		sajax_requests[id].abort();
		sajax_requests.splice(id, 1, null);
	}
}

//Support IE 5
if (typeof(encodeURIComponent) == "undefined") {
	encodeURIComponent = function(string) {
		this.encodeChar = function(c) {
			c = c.charCodeAt(0);
			var utf8 = "";
			if (c < 128) {
				utf8 += String.fromCharCode(c);
			} else if((c > 127) && (c < 2048)) {
				utf8 += String.fromCharCode((c >> 6) | 192);
				utf8 += String.fromCharCode((c & 63) | 128);
			} else {
				utf8 += String.fromCharCode((c >> 12) | 224);
				utf8 += String.fromCharCode(((c >> 6) & 63) | 128);
				utf8 += String.fromCharCode((c & 63) | 128);
			}
			var encoded = "";
			for(var i = 0; i < utf8.length; i++) {
				encoded += "%"+utf8.charCodeAt(i).toString(16).toUpperCase();
			}
			return encoded;
		};
		
		string = string.replace(/\r\n/g,"\n");
		var encoded = "";
		for (var n = 0; n < string.length; n++) {
			if(string.charAt(n).match(/[~!*()'a-z0-9]/i) === null) {
				encoded += encodeChar(string.charAt(n));
			} else {
				encoded += string.charAt(n);
			}
		}
		
		return encoded;
	};
}

//Support IE 5, 5.5 and 6
if (typeof(window.XMLHttpRequest) == "undefined") {
	window.XMLHttpRequest = function() {
		var msxmlhttp = Array(
			'Msxml2.XMLHTTP.6.0',
			'Msxml2.XMLHTTP.5.0',
			'Msxml2.XMLHTTP.4.0',
			'Msxml2.XMLHTTP.3.0',
			'Msxml2.XMLHTTP',
			'Microsoft.XMLHTTP');
		for (var i = 0; i < msxmlhttp.length; i++) {
			try { return new window.ActiveXObject(msxmlhttp[i]); }
			catch(e) {}
		}
		return null;
	};
}

function sajax_do_call(func_name, args, method, asynchronous, uri) {
	
	//Handle old code calls
	switch(arguments.length) {
		case 0:
			return false;
		case 1:
			var args = [];
		case 2:
			var method = "GET";
		case 3:
			var asynchronous = true;
		case 4:
			var uri = "";
	}
	
	if(sajax_request_type != "") {
		method = sajax_request_type;
	}
	
	if(method !== "POST") {
		method = "GET";
	}
	
	if(sajax_remote_uri != "") {
		uri = sajax_remote_uri;
	}
	
	if(uri == "") {
		uri = window.location.href.replace(/#.*$/, "");
	}
	
	var i, x;
	var geturi = "";
	var data;
	var target_id = sajax_target_id;
	var argsarray = Array();
	
	sajax_debug("in sajax_do_call().." + method + "/" + sajax_target_id);
	
	for(i = 0; i < args.length-1; i++) {
		argsarray[i] = args[i];
	}
	
	data = "rs=" + encodeURIComponent(func_name);
	if(argsarray.length > 0) {
		try {
			//the ending & is here to avoide issues with safari 1.2 appending junk on POST
			data += "&rsargs=" + encodeURIComponent(JSON.stringify(argsarray)) + '&';
		} catch(e) {
			return sajax_failure("JSON.stringify() failed for user agent:\n" + navigator.userAgent);
		}
	}
	
	try {
		x = new window.XMLHttpRequest();
	} catch(e) {}
	if(x === null || typeof x.readyState !== "number") {
		//TODO support iframe ajaxing
		//document.getElementsByTagName("pre")[0].innerHTML
		return sajax_failure("NULL sajax object for user agent:\n" + navigator.userAgent);
	}
	
	if(method == "POST" && typeof x.setRequestHeader == "undefined") {
		//TODO convert uri to absolute uri
		if((uri+data).length < 512) {
			sajax_debug("Browser did not support POST, switching to GET");
			method = "GET";
		} else {
			return sajax_failure("Request failed for user agent:\n" + navigator.userAgent);
		}
	}
	
	if (method == "GET") {
		geturi = uri;
		if (geturi.indexOf("?") == -1) {
			geturi += "?" + data;
		} else {
			geturi += "&" + data;
		}

		if(geturi.length > 512){
			method = "POST";
			sajax_debug("Data to long for GET switching to POST");
		} else {
			uri = geturi;
			data = null;
		}
	}
	
	x.open(method, uri, asynchronous);
	
	if (method == "POST") {
		x.setRequestHeader("Method", "POST " + uri + " HTTP/1.1");
		x.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	}
	
	//Prevent Opera from executing the callback multiple times.
	var alreadydone = false;
	var responcefunc = function() {
		if (alreadydone === true)  {
			return false;
		}
		
		if (x.readyState != 4) {
			return false;
		}
		
		var status;
		var data;
		var txt = x.responseText.replace(/^\s*|\s*$/g,"");
		status = txt.charAt(0);
		if(status == "-" || status == "+") {
			data = txt.substring(2);
		} else {
			data = txt;
		}

		if(status == "" && (x.status == 200 || x.status == "" || x.status == "12019")) {
			// let's just assume this is a pre-response bailout and let it slide for now
			return false;
		} else if(status != "+" || x.status != 200) {
			alert("Error " + x.status + ": " + data);
			return false;
		} else {
			alreadydone = true;
			var callback;
			var extra_data = false;
			if (typeof args[args.length-1] == "object") {
				callback = args[args.length-1].callback;
				extra_data = args[args.length-1].extra_data;
			} else {
				callback = args[args.length-1];
			}
			try {
				if(typeof(JSON) != "undefined" && typeof(JSON.parse) != "undefined") {
					try {
						var res = JSON.parse(data);
					} catch(e) {
						return sajax_failure("JSON.parse failed for user agent:\n" + navigator.userAgent);
					}
				} else {
					sajax_debug("Warning: JSON is being directly executed via eval()!");
					eval("var res = ("+data+"); res;");
				}
				if(target_id) {
					document.getElementById(target_id).innerHTML = res;
				} else {
					callback(res, extra_data);
				}
				sajax_requests.splice(id, 1, null);
			} catch(e) {
				sajax_debug("Caught error " + e + ": Could not parse " + data );
				return false;
			}
		}
		return true;
	};
	
	if(asynchronous) {
		x.onreadystatechange = responcefunc;
	}
	
	sajax_debug(func_name + " uri = " + uri + "/post = " + data);
	try {
		x.send(data);
	}
	catch(e) {
		if(method === "POST" && geturi === "") {
			sajax_debug("Browser did not support POST, tyring GET instead");
			sajax_request_type = "";
			return sajax_do_call(func_name, args, "GET", asynchronous);
		} else {
			return sajax_failure("Request failed for user agent:\n" + navigator.userAgent);
		}
	}
	sajax_debug(func_name + " waiting..");
	
	if(asynchronous) {
		var id = sajax_requests.length;
		sajax_requests[id] = x;
		return id;
	} else {
		return responcefunc();
	}
}