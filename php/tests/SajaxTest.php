<?php

use Sajax\Sajax;

class SajaxTest extends PHPUnit_Framework_TestCase
{
    public function test_handleClientRequest()
    {
        Sajax::handleClientRequest();
    }

    public function test_export()
    {
        function test() {}
        Sajax::export(['test' => []]);
    }

    /**
     * @expectedException Exception
     */
    public function test_export_does_not_exists()
    {
        Sajax::export(['does_not_exists' => []]);
    }

    public function test_showJavascript()
    {
        ob_start();
        Sajax::showJavascript();
        $output = ob_get_clean();

        $expected = 'function x_test() {return sajax.doCall("test", arguments, "GET", true, "");}';
        $this->assertEquals($expected, $output);
    }
}
