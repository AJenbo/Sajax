<?php namespace Sajax;

use \Exception;

class Sajax
{
    public static $debugMode = false;
    public static $remoteUri = '';
    public static $failureRedirect = '';
    public static $requestType = 'GET';
    private static $functions = [];
    private static $jsHasBeenShown = false;

    public static function handleClientRequest(bool $bustCache = true)
    {
        if (empty($_GET['rs']) && empty($_POST['rs'])) {
            return;
        }

        ob_start();

        if ($bustCache && !empty($_GET['rs'])) {
            // Always call server
            header('Cache-Control: max-age=0, must-revalidate'); // HTTP/1.1
            header('Pragma: no-cache');
        }
        header('Content-Type: text/plain; charset=UTF-8');

        $funcName = $_GET['rs'] ?? $_POST['rs'];
        $args = $_GET['rsargs'] ?? $_POST['rsargs'] ?? [];
        if ($args) {
            $args = json_decode($args, true);
        }

        $result = call_user_func_array($funcName, $args);
        $error = $funcName . ' not callable';
        if (isset(self::$functions[$funcName])) {
            $result = call_user_func_array($funcName, $args);
            $error = ob_get_contents() ?: '';
            ob_end_clean();
        }

        echo $error ? '-:' . $error : '+:' . json_encode($result);
        exit;
    }

    public static function showJavascript()
    {
        if (self::$jsHasBeenShown) {
            return;
        }
        self::$jsHasBeenShown = true;

        echo 'sajax.debugMode=' . json_encode(self::$debugMode)
            . ';sajax.failureRedirect = ' . json_encode(self::$failureRedirect) . ';';
        foreach (self::$functions as $function => $options) {
            echo 'function x_' . $function . '() {return sajax.doCall("' . $function
                . '", arguments, "' . $options['method'] . '", ' . ($options['asynchronous'] ? 'true' : 'false')
                . ', "' . $options['uri'] . '");}';
        }
    }

    public static function export($functions)
    {
        if (!is_array($functions)) {
            $functions = [];
            foreach (func_num_args() as $function) {
                $functions[$function] = [];
            }
        }

        foreach ($functions as $function => $options) {
            if (!function_exists($function) && empty($options['uri'])) {
                throw new Exception('SAJAX: Cannot export "' . $function . '" as it doesn\'t exists!');
            }

            if (empty($options['method'])) {
                $options['method'] = self::$requestType;
            }

            if (!isset($options['asynchronous'])) {
                $options['asynchronous'] = true;
            }

            if (empty($options['uri'])) {
                $options['uri'] = self::$remoteUri;
            }

            self::$functions[$function] = $options;
        }
    }
}
