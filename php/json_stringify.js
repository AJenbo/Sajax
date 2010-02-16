//http://www.sitepoint.com/blogs/2009/08/19/javascript-json-serialization/
//This might not produce as valid JSON as http://www.JSON.org/json2.js 2009-08-17
var JSON = JSON || {};

// implement JSON.stringify serialization
JSON.stringify = JSON.stringify || function (obj) {
	var t = typeof (obj);
	if(t == "undefined") {
		return;
	} else if(typeof obj.toJSON != "undefined") {
		obj = obj.toJSON();
		if(typeof (obj) == "string")
			obj = '"'+obj.replace(/"/g, '\\"')+'"';
		return String(obj);
	} else if (t != "object" || obj === null) {
		// simple data type
		if (t == "string")
			obj = '"'+obj.replace(/"/g, '\\"')+'"';
		
		return String(obj);
	} else {
		// recurse array or object
		var n, v, json = [], arr = (obj && obj.constructor == Array);
		
		for (n in obj) {
			v = JSON.stringify(obj[n]);
			json[json.length] = (arr ? "" : '"' + n + '":') + String(v);
		}
	
		return (arr ? "[" : "{") + String(json) + (arr ? "]" : "}");
	}
};

if (typeof Date.prototype.toJSON == 'undefined') {
	Date.prototype.toJSON = function (key) {
		return isFinite(this.valueOf()) ? this.getUTCFullYear() + '-' + f(this.getUTCMonth() + 1) + '-' + f(this.getUTCDate()) + 'T' + f(this.getUTCHours()) + ':' + f(this.getUTCMinutes()) + ':' + f(this.getUTCSeconds()) + 'Z' : null
	};
	String.prototype.toJSON = Number.prototype.toJSON = Boolean.prototype.toJSON = function (key) {
		return this.valueOf();
	}
}