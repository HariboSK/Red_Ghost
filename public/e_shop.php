<?php
// Public entry point when VirtualHost DocumentRoot points to /public.
require_once dirname(__DIR__) . '/app/core/function.php';
app_register_error_handlers();
include dirname(__DIR__) . '/app/views/partials/e_shop.php';
