<?php

class App
{
    private static bool $initialized = false;

    public static function init(): void
    {
        if (self::$initialized) {
            return;
        }

        $projectRoot = dirname(__DIR__, 2);

        self::loadCore($projectRoot);
        self::loadModels($projectRoot);

        SessionManager::start();
        app_env_load($projectRoot . '/config/config.env');
        app_register_error_handlers();

        self::$initialized = true;
    }

    private static function loadCore(string $projectRoot): void
    {
        // Load database configuration FIRST
        require_once $projectRoot . '/config/config.php';

        $coreFiles = [
            '/app/core/middleware/function.php',
            '/app/core/SessionManager.php',
            '/app/core/session_helper.php',
            '/app/core/Redirect.php',
            '/app/core/helper.php',
            '/app/core/assetHelper.php',
            '/app/core/shopService.php',
            '/app/core/dashboard_helper.php',
            '/app/core/authView.php',
            '/app/core/router.php',
        ];

        foreach ($coreFiles as $file) {
            require_once $projectRoot . $file;
        }
    }

    private static function loadModels(string $projectRoot): void
    {
        $modelFiles = [
            '/app/models/base.model.php',
            '/app/models/product.model.php',
            '/app/models/contact_message.model.php',
            '/app/models/discount_code.model.php',
            '/app/models/user.model.php',
            '/app/models/order.model.php',
            '/app/models/payment.model.php',
        ];

        foreach ($modelFiles as $file) {
            $absolutePath = $projectRoot . $file;

            if (is_file($absolutePath)) {
                require_once $absolutePath;
            }
        }
    }
}
