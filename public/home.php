<?php
// Front controller: load helpers and dispatch current request.
require_once dirname(__DIR__) . '/app/core/function.php';
app_register_error_handlers();
require_once dirname(__DIR__) . '/app/core/router.php';

//toto musi byt posledne lebo sa to testuje error handlerom
$router = new Router();
$router->dispatch();