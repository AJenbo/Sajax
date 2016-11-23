<?php

/**
 * Dummy function
 *
 * @return string
 */
function otherFucntion(): string
{
    return 'URI: ' . $_SERVER['PHP_SELF'];
}


// Include the libery
require_once 'Sajax.php';

// Export the multiply with default options (empty array as value)
Sajax\Sajax::export(['otherefucntion' => []]);

// Handel the ajax request, script will exit here on ajax calls
Sajax\Sajax::handleClientRequest();
