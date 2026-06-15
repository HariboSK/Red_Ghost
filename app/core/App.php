<?php
declare(strict_types=1);

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
        app_register_error_handlers();

        self::$initialized = true;
    }

    private static function loadCore(string $projectRoot): void
    {
        // Načítanie konfiguračného súboru
        require_once $projectRoot . '/config/config.php';

        $coreFiles = [
            '/app/core/AssetHelper.php',
            '/app/core/AuthView.php',
            '/app/core/DashboardHelper.php',
            '/app/core/Flash.php',
            '/app/core/Helper.php',
            '/app/core/LoginRegister.php',
            '/app/core/Redirect.php',
            '/app/core/ResetPassword.php',
            '/app/core/Router.php',
            '/app/core/SessionHelper.php',
            '/app/core/SessionManager.php',
            '/app/core/ShopService.php',
            '/app/core/UploadAvatar.php',
            '/app/core/CartService.php',
            '/app/core/ProductController.php',
            '/app/core/PaymentController.php',
            '/app/core/ForgotPasswordController.php',
            '/app/core/middleware/Function.php',
        ];

        foreach ($coreFiles as $file) {
            require_once $projectRoot . $file;
        }
    }

    private static function loadModels(string $projectRoot): void
    {
        $modelFiles = [
            '/app/models/AvatarManager.php',
            '/app/models/BaseModel.php',
            '/app/models/ContactMessageModel.php',
            '/app/models/DiscountCodeModel.php',
            '/app/models/OrderModel.php',
            '/app/models/ProductModel.php',
            '/app/models/ProductReviewModel.php',
            '/app/models/ShopReviewModel.php',
            '/app/models/UserModel.php',
            '/app/models/UserEditModule.php',
            '/app/models/UserProfileModule.php',
        ];

        foreach ($modelFiles as $file) {
            $absolutePath = $projectRoot . $file;

            if (is_file($absolutePath)) {
                require_once $absolutePath;
            }
        }
    }
}
