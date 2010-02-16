<%	

function r(str)
{
	Response.write("<br>-"+str+"-<br>")
}

	function Sajax(debug_mode)
	{
		this.debug_mode = debug_mode||false;
		this.export_list = [];
		this.js_has_been_shown = false;
        
		this.handle_client_request = function()
		{
			func_name = Request.QueryString("rs");
			if(!func_name||String(func_name)=="undefined") return false
			
			//bust cache
			Response.AddHeader("Expires", "Mon, 26 Jul 1997 05:00:00 GMT")
            //always modified
			Response.AddHeader("Last-Modified", new Date().toGMTString().replace(/UTC/,"GMT"))
			//HTTP/1.1
            Response.AddHeader("Cache-Control", "no-cache, must-revalidate")
			//HTTP/1.0
            Response.AddHeader("Pragma", "no-cache")
            
            
            
			if(this.export_list[func_name]=="undefined")
			{
				result = "-"+func_name+" is not callable";
                //if(this.debug_mode) Response.write("-"+func_name+" is not callable");
			}
			else
			{
				rsargs_raw = Request.QueryString("rsargs[]")

                rsargs = []
                for(rs_i=1;rs_i<=rsargs_raw.Count;rs_i++)
                {
                    rsargs[rs_i-1]=rsargs_raw(rs_i)
                }

               // if(this.debug_mode) Response.write("calling " + func_name + "(" + 
               //                                    rsargs.join(",")+")")
				
                eval_str = this.export_list[func_name]+"("+rsargs+")"
                
                try
                {
                    result = "+"+eval(eval_str);
                }
                catch(e)
                {
                    result = "-"+e.message+" (x_"+eval_str+")";
                }
			}
			
            Response.write(result+"[sajax_result________end]]")
			//exit()
		}
		
		this.show_common_js = function()
		{
			js_debug_mode = this.debug_mode
	    	js_string_out = "      // remote scripting library\n" +
							"      // (c) copyright 2005 modernmethod, inc\n" +
							"      var rs_debug_mode = "+js_debug_mode+";\n" +
							"      var rs_obj = false;\n" +
							"      var rs_callback = false;\n" +
							"      \n" +
							"      function rs_debug(text) {\n" +
							"         if (rs_debug_mode)\n" +
							"            alert('RSD: ' + text)\n" +
							"      }\n" +
							"      \n" +
							"       function rs_init_object() {\n" +
							"          rs_debug('rs_init_object() called..')\n" +
							"          \n" +
							"          var A;\n" +
							"         try {\n" +
							"            A=new ActiveXObject('Msxml2.XMLHTTP');\n" +
							"         } catch (e) {\n" +
							"            try {\n" +
							"               A=new ActiveXObject('Microsoft.XMLHTTP');\n" +
							"            } catch (oc) {\n" +
							"               A=null;\n" +
							"            }\n" +
							"         }\n" +
							"         if(!A && typeof XMLHttpRequest != 'undefined')\n" +
							"            A = new XMLHttpRequest();\n" +
							"         if (!A)\n" +
							"            rs_debug('Could not create connection object.');\n" +
							"         return A;\n" +
							"      }\n" 

			Response.write(js_string_out)
		}

		this.rs_esc=function(val)
		{
			
		}
		
		this.export_function = function()
		{
			for(var i=0;i<arguments.length;i++)
			{
				this.export_list[arguments[i]] = arguments[i];
			}
		}
		
		this.show_javascript = function()
		{
			if(!this.js_has_been_shown)
			{
				this.show_common_js()
				this.js_has_been_shown = true
			}
		
			for(func_name in this.export_list)
			{
				this.show_one(func_name);
			}
		}
		
		this.rs_esc = function(val)
		{
			var dqex = /\"/g //"
			return val.replace(dqex, '\\\\"') 
		}
		
		this.show_one=function(func_name)
		{
			var svr, srl, qsr; 
			
			svr = Request.ServerVariables("SERVER_NAME")
			srl = Request.ServerVariables("URL")
			qsr = Request.ServerVariables("QUERY_STRING");
			uri = svr + srl + qsr;

			if(!(uri.indexOf('?')>-1))
			{
				uri += "?rs=" +  escape(func_name)
			}
			else
			{
				uri += "&rs=" + escape(func_name)
			}

		    escapeduri = this.rs_esc(uri)
			js_string_out = "   // wrapper for "+func_name+"\n" +
							"   function x_"+func_name+"(){\n" +
							"      // count args; build URL\n" +
							"         var i, x, n;\n" +
							"         //var url = 'http://"+escapeduri+"', a = x_"+func_name+".arguments;\n" +
							"         url = 'http://"+escapeduri+"', a = x_"+func_name+".arguments;\n" +
							"         for (i = 0; i < a.length-1; i++)\n" +
							"            url = url + '&rsargs[]=' + escape(a[i]);\n" +
							"         x = rs_init_object();\n" +
							"         x.open('GET', url, true);\n" +
							"         x.onreadystatechange = function() {\n" +
							"            if (x.readyState != 4)\n" +
							"               return;\n" +
							"            rs_debug('received ' + x.responseText);\n" +
							"            \n" +
							"            var status;\n" +
							"            var data;\n" +
							"            status = x.responseText.charAt(0);\n" +
							"            data = x.responseText;\n" +
                            "            var pos = data.indexOf('[sajax_result________end]]');\n " +
							"            if (status == '-'){\n" +
							"               if("+this.debug_mode+"){alert('Error: ' + data.substring(1,pos));}}\n" +
							"            else \n" +
							"               a[a.length-1](data.substring(1,pos));\n" +
							"         }\n" +
							"         x.send(null);\n" +
							"         rs_debug('x_"+func_name+" url = ' + url);\n" +
							"         rs_debug('x_"+func_name+" waiting..');\n" +
							"      }\n" 
		
			Response.write(js_string_out)
		}
	}
%>