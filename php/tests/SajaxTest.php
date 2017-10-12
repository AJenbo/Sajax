<?php

use Sajax\Sajax;

class SajaxTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Sajax::$failureRedirect = 'https://github.com/AJenbo/Sajax';
        Sajax::$remoteUri = 'https://github.com/AJenbo/Sajax/';
        Sajax::$debugMode = true;
        Sajax::$testMode = true;
    }

    public function test_handleClientRequest()
    {
        Sajax::handleClientRequest();
    }

    public function test_export()
    {
        function test($string) {return 'String was ' . $string;}
        Sajax::export(['test' => []]);
    }

    public function test_handleClientRequest_()
    {
        $_GET['rs'] = 'test';
        $_GET['rsargs'] = '["test"]';
        ob_start();
        Sajax::handleClientRequest();
        $output = ob_get_clean();

        $expected = '+:"String was test"';
        $this->assertEquals($expected, $output);
    }

    /**
     * @expectedException Sajax\SajaxException
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

        $expected = 'sajax.debugMode=!0;sajax.failureRedirect="https:\/\/github.com\/AJenbo\/Sajax";function x_test(){return sajax.doCall("test",arguments,"GET",!0,"https:\/\/github.com\/AJenbo\/Sajax\/")}';
        $this->assertEquals($expected, $output);
    }

    public function test_showJavascript_repeat()
    {
        ob_start();
        Sajax::showJavascript();
        $output = ob_get_clean();

        $expected = '';
        $this->assertEquals($expected, $output);
    }
}
