<?php
// Front controller: load helpers and dispatch current request.
require_once dirname(__DIR__) . '/app/core/function.php';
require_once dirname(__DIR__) . '/app/core/router.php';

$router = new Router();
$router->dispatch();
