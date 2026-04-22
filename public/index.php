<?php
// Main front controller for all web routes.
require_once dirname(__DIR__) . '/app/core/App.php';
App::init();

$router = new Router();
$router->dispatch();
