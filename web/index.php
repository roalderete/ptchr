<?php

include_once('../src/config/log_settings.php');

use OrangeHRM\Config\Config;
use OrangeHRM\Framework\Framework;
use OrangeHRM\Framework\Http\RedirectResponse;
use OrangeHRM\Framework\Http\Request;
use Symfony\Component\ErrorHandler\Debug;

require realpath(__DIR__ . '/../src/vendor/autoload.php');

$env = 'prod';
$debug = 'prod' !== $env;

if ($debug) {
    umask(0000);
    Debug::enable();
}

$kernel = new Framework($env, $debug);
$request = Request::createFromGlobals();

if (Config::isInstalled()) {
    $response = $kernel->handleRequest($request);
} else {
    $response = new RedirectResponse(str_replace('/web/index.php', '', $request->getBaseUrl()));
}

$response->send();
$kernel->terminate($request, $response);
