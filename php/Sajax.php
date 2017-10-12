<?php namespace Sajax;

/**
 * Sajax class.
 */
class Sajax
{
    /**
     * Are we in debug mode.
     */
    public static $debugMode = false;

    /**
     * Are we in debug mode.
     */
    public static $testMode = false;

    /**
     * Default to this url for requests.
     */
    public static $remoteUri = '';

    /**
     * Redirect to this url on failure.
     */
    public static $failureRedirect = '';

    /**
     * Default request type.
     */
    public static $requestType = 'GET';

    /**
     * Exported functions.
     */
    private static $functions = [];

    /**
     * Has the JS been printed already.
     */
    private static $jsHasBeenShown = false;

    /**
     * Handel ajax request from the browser.
     *
     * @param bool $bustCache Permit peropper cache header in scripts
     */
    public static function handleClientRequest(bool $bustCache = true): void
    {
        if (empty($_GET['rs']) && empty($_POST['rs'])) {
            return; // This is not a Ajax request, return to parent script
        }

        ob_start(); // Capture all output

        if (!self::$testMode) {
            if ($bustCache && !empty($_GET['rs'])) {
                // Always call server
                header('Cache-Control: max-age=0, must-revalidate'); // HTTP/1.1
                header('Pragma: no-cache');
            }
            header('Content-Type: text/plain; charset=UTF-8');
        }
        // Get request data
        $funcName = $_GET['rs'] ?? $_POST['rs'];
        $args = $_GET['rsargs'] ?? $_POST['rsargs'] ?? [];
        if ($args) {
            $args = json_decode($args, true);
        }

        $result = [];
        $error = $funcName . ' not callable';
        if (isset(self::$functions[$funcName])) {
            $result = call_user_func_array($funcName, $args); // Execute exported function
            $error = ob_get_contents() ?: ''; // If there where any output during execution we return it as an error
            ob_end_clean();
        }

        // Print result
        echo $error ? '-:' . $error : '+:' . json_encode($result);
        if (!self::$testMode) {
            exit; // End execution
        }
    }

    /**
     * Print the JS code for interacting with the exported functions.
     */
    public static function showJavascript(bool $return = false): ?string
    {
        if (!$return) {
            if (self::$jsHasBeenShown) {
                return null; // JS was already printed, do nothing
            }
            self::$jsHasBeenShown = true;
        }

        $js = '';

        // Put client in debug mode
        if (self::$debugMode) {
            $js .= 'sajax.debugMode=!0;';
        }

        // Set failure url
        if (self::$failureRedirect) {
            $js .= 'sajax.failureRedirect=' . json_encode(self::$failureRedirect) . ';';
        }

        // Print JS for each individual exported function
        foreach (self::$functions as $function => $options) {
            $js .= 'function x_' . $function . '(){return sajax.doCall(' . json_encode($function)
                . ',arguments,' . json_encode($options['method']) . ',' . ($options['asynchronous'] ? '!0' : '!1')
                . ',' . json_encode($options['uri']) . ')}';
        }

        if (!$return) {
            echo $js;

            return null;
        }

        return $js;
    }

    /**
     * Export functions.
     *
     * @param array $functions Functions as key options as an assigned array
     */
    public static function export(array $functions): void
    {
        foreach ($functions as $function => $options) {
            // Chec if function exists, but only if it's a local url
            if (!function_exists($function) && empty($options['uri'])) {
                throw new SajaxException('SAJAX: Cannot export "' . $function . '" as it doesn\'t exists!');
            }

            // Set defaults if options not specefied
            $options['method'] = $options['method'] ?? null ?: self::$requestType;
            $options['asynchronous'] = (bool) ($options['asynchronous'] ?? true);
            $options['uri'] = $options['uri'] ?? null ?: self::$remoteUri;

            self::$functions[$function] = $options;
        }
    }
}
