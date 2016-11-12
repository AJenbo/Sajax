<?php
function otherefucntion()
{
    return 'URI: ' . $_SERVER['PHP_SELF'];
}

require_once 'Sajax.php';
Sajax\Sajax::export(['otherefucntion' => []]);
Sajax\Sajax::handleClientRequest();
