<?php

use App\Kernel;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__.'/vendor/autoload_runtime.php';

return function (array $context) {
    // This script explicitly serves the legacy PLC data endpoint at the root.
    // We rewrite the request URI dynamically to the internal symfony route so that 
    // the application router processes it exactly as a normal request.
    
    $_SERVER['REQUEST_URI'] = '/api/device/plc';
    
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
