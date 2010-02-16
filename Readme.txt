WELCOME TO SAJAX
----------------

Sajax is a cross-platform, cross-browser web scripting toolkit
that makes it easy to expose functions in your code to JavaScript.

For more information about Sajax, please see the homepage:

	http://sajax.info/

In this archive you will find a folder for each platform that is
currently supported. Please see individual documentation in each
folder for specific errata.



As of 0.13 a JavaScript implementation of JSON.stringify() has
become a requirement when sending data for browsers that does not
have this nativly (IE8 or FF3.5). JSON2 from
http://www.json.org/js.html has been included for this reason.

It is highly recomented to also include a JSON.parse()
implementation for better security for the client, especialy when
handeling data from othere sites, json.parse.js is the
implementation from JSON2, unfortunatly this breaks Safari 1.3.2
json_parse_state.js seams to be the most compatible but
json_parse.js might also be worth checking out.