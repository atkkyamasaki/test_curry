<?php

// Detect application environment
if ($_SERVER['SERVER_ADDR'] === 'localhost.api-test' || $_SERVER['SERVER_ADDR'] === '192.168.242.100') {
    // Local Test
    // For Apache
//    putenv('APP_ENV=test');
    // For Nginx
    $appEnv = 'test';
} elseif ($_SERVER['SERVER_ADDR'] === '10.1.1.201') {
    // ShowRoom
    // For Apache
//    putenv('APP_ENV=develop');
    // For Nginx
    $appEnv = 'develop';
} elseif ($_SERVER['SERVER_ADDR'] === '150.87.0.111') {
    // Console Server
    // For Apache
//    putenv('APP_ENV=product');
    // For Nginx
    $appEnv = 'product';
}

define('CURRY_PATH', realpath(dirname(__FILE__) . '/../../curry'));
define('SITE_PATH', realpath(dirname(__FILE__) . '/..'));

// Add framework path to include path of php.
set_include_path(implode(PATH_SEPARATOR, array(
    CURRY_PATH,
    get_include_path(),
)));

// Set autoloader.
require_once CURRY_PATH . '/core/loader.php';
spl_autoload_register('Loader::autoload');
//require_once CURRY_PATH . '/load_core.php';
// Set directory settings.
PathManager::setFrameworkRoot(CURRY_PATH);
PathManager::setSystemRoot(SITE_PATH);

// Load validator extension
Loader::loadLibrary('ValidatorExtension');

// Execute dispatch process.
$dispatcher = new Dispatcher();
// For Apache
//$dispatcher->setAppEnv(getenv('APP_ENV'));
// For Nginx
$dispatcher->setAppEnv($appEnv);
$dispatcher->setValidatorClass('ValidatorExtension');
$dispatcher->dispatch();
