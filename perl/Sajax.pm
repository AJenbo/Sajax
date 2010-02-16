package Sajax;
use Data::Dumper;

my $rs_debug_mode = 0;
my $rs_js_has_been_shown = 0;
my %rs_export_list = ();
my %rs_coderef_list = ();

sub incl_sajax {
}
sub rs_init {
    $rs_debug_mode = 0;
    $rs_js_has_been_shown = 0;
    %rs_export_list = ();
    %rs_coderef_list = ();

}
	
sub rs_handle_client_request {
    my($q)=@_;
    my $rv="";
    
    if (!defined $q->param("rs")) {
	return undef;
    }
    
    my $func_name = $q->param("rs");

    if ( defined $rs_export_list{$func_name}) {
	$rv .= "+:";
	eval {
	    $rv .= &$func_name($q->param("rsargs"));
	};
	if($@) {
	    print STDERR "Err:[$@]\n";
	}
    } elsif ( defined $rs_coderef_list{$func_name}) {
	$rv .= "+:";
	my $cr = $rs_coderef_list{$func_name};
	eval {
	    $rv .= &$cr($q->param("rsargs"));
	};
	if($@) {
	    print STDERR "Err:[$@]\n";
	}
    } else {
	$rv .= "-:$func_name not callable";
    }
    
    return $rv;
}
	
sub rs_show_common_js() {
    my $rv = "";
    my $debug_mode = $rs_debug_mode ? "true" : "false";
    my $CC = "\n// Perl backend version (c) copyright 2005 Nathan Schmidt";
    $CC = "";
    $rv .= <<EOT;
// remote scripting library
// (c) copyright 2005 modernmethod, inc$CC
var rs_debug_mode = $debug_mode;
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
EOT
    return $rv;
}	


#javascript escape a value
sub rs_esc {
    my ($val)=@_;
    $val =~ s/\"/\\\\\"/;
    return $val;
}	

sub rs_urlencode {
    my($enc) = @_;
    $enc =~ s/^\s+|\s+$//gs;
    $enc =~ s/([^a-zA-Z0-9_\-.])/uc sprintf("%%%02x",ord($1))/eg;
    $enc =~ s/ /\+/g;
    $enc =~ s/%20/\+/g;
    return $enc;
}    

sub rs_show_one {
    my($q,$func_name)=@_;
    my $rv = "";
    my $uri = $q->url(-query=>1);
    if ($uri =~ m/\?/) {
	$uri .= "&rs=".rs_urlencode($func_name);
    } else {
	$uri .= "?rs=".rs_urlencode($func_name);
    }

    my $urie = rs_esc($uri);

    $rv .= <<EOT;	

    // wrapper for $func_name
    function x_$func_name() {
	// count args; build URL
	var i, x, n;
	var url = "$urie", a = x_$func_name.arguments;
	for (i = 0; i < a.length-1; i++) 
	    url = url + "&rsargs=" + escape(a[i]);
	url = url.replace( /[+]/g, '%2B'); // fix the unescaped plus signs 
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
	rs_debug("x_$func_name url = " + url);
	rs_debug("x_$func_name waiting..");
    }


EOT
    return $rv;
}

sub rs_register {
    my($fn,$coderef)=@_;
    $rs_coderef_list{$fn} = $coderef;
}	
sub rs_export {
    map {$rs_export_list{$_}=$_} @_;
    return;
}	

sub rs_show_javascript {
    my ($q) = @_;
    my $rv = "";
    if (! $rs_js_has_been_shown) {
	$rv .= rs_show_common_js();
	$rs_js_has_been_shown = 1;
    }

    foreach my $func (keys %rs_export_list) {
	$rv .= rs_show_one($q,$func);
    }
    foreach my $func (keys %rs_coderef_list) {
	$rv .= rs_show_one($q,$func);
    }

    return $rv;
}

1;
