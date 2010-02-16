#! /usr/bin/perl -w

# Perl version cloned from the original PHP by http://www.modernmethod.com/sajax/
# I've left commented-out examples in the code for running with static methods.
# For moving to ModPerl it's important to no be redefining subs all the time,
# so this code adds the rs_register(funcname,coderef) call, which takes a coderef
# rather than the name of a sub to be called.


use Sajax;
use CGI;

my $q = new CGI;

my $rv = "";

$rv .= "content-type: text/html\n\n";


=pod
#for static method calls
sub Sajax::multiply {
    my($x, $y)=@_;
    return $x * $y;
}
sub Sajax::divide {
    my($x, $y)=@_;
    return $x / $y;
}
=cut

#equivalent modperl methods
my $msub = sub {
    my($x, $y)=@_;
    return $x * $y;
};
my $dsub = sub {
    my($x, $y)=@_;
    return $x / $y;
};



Sajax::rs_init();

#register static methods (called by name)
#Sajax::rs_export("multiply","divide");

#modperl methods (called as anonymous coderefs)
Sajax::rs_register("multiply",$msub);
Sajax::rs_register("divide",$dsub);


my $handled_value = Sajax::rs_handle_client_request($q);

if(defined $handled_value) {
    $rv .= $handled_value;
} else {

$rv .= "<html>\n<head>\n<title>Multiplier</title>\n<script>\n\n";

$rv .= Sajax::rs_show_javascript($q);

$rv .= <<EOT;
function do_multiply_cb(z) {
    document.getElementById("z").value = z;
}
function do_divide_cb(z) {
    document.getElementById("zz").value = z;
}


function do_multiply() {
    var x, y;
    x = document.getElementById("x").value;
    y = document.getElementById("y").value;
    x_multiply(x, y, do_multiply_cb);
}

function do_divide() {
    var x, y;
    x = document.getElementById("x").value;
    y = document.getElementById("y").value;
    x_divide(x, y, do_divide_cb);
}
EOT


$rv .= "</script>\n\n</head>\n";
$rv .= <<EOT;
<body>
        <input type="text" name="x" id="x" value="2" size="3">
        *
        <input type="text" name="y" id="y" value="3" size="3">
        =
        <input type="text" name="z" id="z" value="" size="5">
        <input type="text" name="zz" id="zz" value="" size="5">
        <input type="button" name="check" value="Multiply" onclick="do_multiply(); return false;">
        <input type="button" name="check" value="Divide" onclick="do_divide(); return false;">
        <input type="button" name="check" value="Both" onclick="do_multiply();do_divide(); return false;">

<BR>
    <A HREF=showsource.cgi>Show source</A>
</body>
</html>
EOT
}


print $rv;



