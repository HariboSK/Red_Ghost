<?php
// Main front controller for all web routes.
require_once dirname(__DIR__) . '/app/core/SessionManager.php';
// Start secure session with sensible defaults
SessionManager::start();
require_once dirname(__DIR__) . '/app/core/middleware/function.php';
app_env_load(dirname(__DIR__) . '/config/config.env');
app_register_error_handlers();
require_once dirname(__DIR__) . '/app/core/router.php';

$router = new Router();
$router->dispatch();
