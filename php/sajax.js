// Remote scripting library
// (c) copyright 2005 modernmethod, inc

var sajax = {
	"debugMode": false,
	"failureRedirect": "",
	"remoteUri": "",
	"requestType": "",
	"targetId": "",
	"requests": []
};

sajax.debug = function (text) {
	if (sajax.debugMode) {
		alert(text);
	}
	return true;
}

sajax.failure = function (text) {
	if (sajax.failureRedirect !== "" && !sajax.debugMode) {
		window.location.href = sajax.failureRedirect;
	}

	sajax.debug(text);
	return false;
}

sajax.cancel = function (id) {
	if (arguments.length === 0) {
		for (var i = 0; i < sajax.requests.length; i++) {
			if (sajax.requests[i]) {
				sajax.requests[i].abort();
				sajax.requests.splice(i, 1, null);
			}
		}
	} else if (sajax.requests[id]) {
		sajax.requests[id].abort();
		sajax.requests.splice(id, 1, null);
	}
}

sajax.doCall = function (funcName, args, method, asynchronous, uri) {
	var i, x, data;
	var targetId = sajax.targetId;
	var argsarray = [];

	if (asynchronous) {
		var id = sajax.requests.length;
	}

	if (sajax.requestType !== "") {
		method = sajax.requestType;
	}

	if (method !== "POST") {
		method = "GET";
	}

	if (sajax.remoteUri !== "") {
		uri = sajax.remoteUri;
	}

	if (uri === "") {
		uri = window.location.href.replace(/#.*$/, "");
	}

	sajax.debug("in sajax.doCall().." + method + "/" + sajax.targetId);

	for(i = 0; i < args.length-1; i++) {
		argsarray[i] = args[i];
	}

	data = "rs=" + encodeURIComponent(funcName);
	if (argsarray.length > 0) {
		try {
			//the ending & is here to avoide issues with safari 1.2 appending junk on POST
			data += "&rsargs=" + encodeURIComponent(JSON.stringify(argsarray)) + "&";
		} catch(e) {
			return sajax.failure("JSON.stringify() failed for user agent:\n" + navigator.userAgent);
		}
	}

	try {
		x = new window.XMLHttpRequest();
	} catch(e) {}
	if (x === null || typeof x.readyState !== "number") {
		//TODO support iframe ajaxing
		//document.getElementsByTagName("pre")[0].innerHTML
		return sajax.failure("NULL sajax object for user agent:\n" + navigator.userAgent);
	}

	if (method === "GET" && (uri + data).length > 512) {
		method = "POST";
		sajax.debug("Data to long for GET switching to POST");
	}

	if (method === "POST" && typeof x.setRequestHeader === "undefined") {
		//TODO convert uri to absolute uri
		if ((uri + data).length < 512) {
			sajax.debug("Browser did not support POST, switching to GET");
			method = "GET";
		} else {
			return sajax.failure("Request failed for user agent:\n" + navigator.userAgent);
		}
	}

	if (method === "GET") {
		uri += ((uri.indexOf("?") === -1) ? "?" : "&") + data;
		data = null;
	}

	x.open(method, uri, asynchronous);

	if (method === "POST") {
		x.setRequestHeader("Method", "POST " + uri + " HTTP/1.1");
		x.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	}

	//Prevent Opera from executing the callback multiple times.
	var alreadydone = false;
	var responcefunc = function() {
		if (alreadydone === true || x.readyState !== 4) {
			return false;
		}

		var data = x.responseText.replace(/^\s*|\s*$/g, "");
		var status = data.charAt(0);
		if (status === "-" || status === "+") {
			data = data.substring(2);
		}

		if (status === "" && (x.status === 200 || x.status === "" || x.status === "12019")) {
			// let's just assume this is a pre-response bailout and let it slide for now
			return false;
		}

		if (status !== "+" || x.status !== 200) {
			alert("Error " + x.status + ": " + data);
			return false;
		}

		alreadydone = true;
		var extraData = false;
		var callback = args[args.length - 1];
		if (typeof callback === "object") {
			extraData = callback.extraData;
			callback = callback.callback;
		}
		try {
			if (typeof(JSON) !== "undefined" && typeof(JSON.parse) !== "undefined") {
				try {
					var res = JSON.parse(data);
				} catch(e) {
					return sajax.failure("JSON.parse failed for user agent:\n" + navigator.userAgent);
				}
			} else {
				sajax.debug("Warning: JSON is being directly executed via eval()!");
				eval("var res = ("+data+"); res;");
			}
			if (targetId) {
				document.getElementById(targetId).innerHTML = res;
			} else {
				callback(res, extraData);
			}
			if (asynchronous) {
				sajax.requests.splice(id, 1, null);
			}
		} catch(e) {
			sajax.debug("Caught error " + e + ": Could not parse " + data );
			return false;
		}

		return true;
	};

	if (asynchronous) {
		x.onreadystatechange = responcefunc;
	}

	sajax.debug(funcName + " uri = " + uri + "/post = " + data);
	try {
		x.send(data);
	} catch(e) {
		if (method === "POST" && uri === "") {
			sajax.debug("Browser did not support POST, tyring GET instead");
			sajax.requestType = "";
			return sajax.doCall(funcName, args, "GET", asynchronous);
		} else {
			return sajax.failure("Request failed for user agent:\n" + navigator.userAgent);
		}
	}
	sajax.debug(funcName + " waiting..");

	if (asynchronous) {
		sajax.requests[id] = x;
		return id;
	}

	return responcefunc();
}

//Support IE 5, 5.5 and 6
if (typeof(window.XMLHttpRequest) === "undefined") {
	window.XMLHttpRequest = function() {
		var msxmlhttp = [
			"Msxml2.XMLHTTP.6.0",
			"Msxml2.XMLHTTP.5.0",
			"Msxml2.XMLHTTP.4.0",
			"Msxml2.XMLHTTP.3.0",
			"Msxml2.XMLHTTP",
			"Microsoft.XMLHTTP"
		];
		for (var i = 0; i < msxmlhttp.length; i++) {
			try { return new window.ActiveXObject(msxmlhttp[i]); }
			catch(e) {}
		}
		return null;
	};
}

//Support IE 5
if (typeof(encodeURIComponent) === "undefined") {
	encodeURIComponent = function(string) {
		this.encodeChar = function(c) {
			c = c.charCodeAt(0);
			var utf8 = "";
			if (c < 128) {
				utf8 += String.fromCharCode(c);
			} else if ((c > 127) && (c < 2048)) {
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
			if (string.charAt(n).match(/[~!*()'a-z0-9]/i) === null) {
				encoded += encodeChar(string.charAt(n));
			} else {
				encoded += string.charAt(n);
			}
		}

		return encoded;
	};
}
