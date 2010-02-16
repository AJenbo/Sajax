#!/usr/bin/env ruby
require 'cgi'
require 'date'

class Sajax
 
  def initialize(debug_mode=false)
    @debug_mode = debug_mode
    @export_list = Hash.new
    @js_has_been_shown = false
    @cgi = CGI.new
  end

  def handle_client_request
    func_name = @cgi.params['rs'].to_s
    if func_name == ""
      return
    end 
    # Bust cache in the head
    @cgi.out({
      "Expires"    => "Mon, 26 Jul 1997 05:00:00 GMT",
      "Last-Modified" => DateTime.now.new_offset.strftime("%a, %d %m %H:%M:%S GMT"), # always modified
      "Cache-Control" => "no-cache, must-revalidate",  # HTTP/1.1
      "Pragma" => "no-cache",  # HTTP/1.0
      })
   
    unless @export_list.has_key?(func_name)
      print "-:%s not callable" % func_name
    else
      print "+:"
      rsargs = @cgi.params['rsargs[]']
      result = __send__(@export_list[func_name],*rsargs)
      print result
    end
    exit()
  end
 
  def show_common_js
    js_debug_mode = @debug_mode.to_s.downcase
    print <<-"EOS"
      // remote scripting library
      // (c) copyright 2005 modernmethod, inc
      var rs_debug_mode = #{js_debug_mode};
      var rs_obj = false;
      var rs_callback = false;
      
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
EOS
  end
 
  def rs_esc(val)
    return val.gsub('"', '\\\\"')   
  end
       
  def show_one(func_name)
    uri = ENV['REQUEST_URI']
    unless uri.include?('?')
      uri += "?rs=%s" % CGI.escape(func_name)
    else
      uri += "&rs=%s" % CGI.escape(func_name)
    end
    escapeduri = rs_esc(uri)
    print <<-"EOS"
   // wrapper for #{func_name}
   function x_#{func_name}(){
      // count args; build URL
         var i, x, n;
         var url = "#{escapeduri}", a = x_#{func_name}.arguments;
         for (i = 0; i < a.length-1; i++)
            url = url + "&rsargs[]=" + escape(a[i]);
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
         rs_debug("x_#{func_name} url = " + url);
         rs_debug("x_#{func_name} waiting..");
      }
     
    EOS
  end
 
  def export(*args)
   for f in args
      @export_list[f] = f       
   end                 
  end
         
  def show_javascript
    unless @js_has_been_shown
      show_common_js()
      @js_has_been_shown = true
    end
    for func_name in @export_list.keys
      show_one(func_name)
    end
  end
               
end
