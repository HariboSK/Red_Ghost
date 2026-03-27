<?php

//funkcia na ziskanie zakladnej cesty aplikacie, aby sme mohli správne generovat URL
function app_base_path() {
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $scriptDir = str_replace('\\', '/', dirname($scriptName));
    $scriptDir = rtrim($scriptDir, '/');

    if ($scriptDir === '.' || $scriptDir === '/' || $scriptDir === '\\') {
        return '';
    }

    return $scriptDir;
}

//kompletne cesty pre odkay v aplikacii
function route($path) {
    if (preg_match('#^(https?:)?//#i', (string) $path) === 1) {
        return htmlspecialchars((string) $path, ENT_QUOTES, 'UTF-8');
    }

    $basePath = app_base_path();
    $normalizedPath = '/' . ltrim((string) $path, '/');

    return htmlspecialchars($basePath . $normalizedPath, ENT_QUOTES, 'UTF-8');
}

//skratka pre app_base_path
function baseUrl() {
    return app_base_path();
}

//Funckie na zachytenie chyb ktore nastanu pri prevadzke aplikacie a ich logovanie do suboru. 
//V pripade fatálnych chyb sa zobrazí užívateľovi přátelská zpráva.  LOGOVANIE CHYB
function app_log($uroven, $sprava, array $context = []) {
    $uroven = strtoupper((string) $uroven);
    $sprava = (string) $sprava;

    $projectRoot = dirname(__DIR__, 2);
    $logDir = $projectRoot . '/storage/logs';
    $logFile = $logDir . '/app-' . date('Y-m-d') . '.log';

    if (!is_dir($logDir)) {
        if (!mkdir($logDir, 0775, true) && !is_dir($logDir)) {
            return false;
        }
    }

    $safeContext = [];
    foreach ($context as $key => $value) {
        if (is_scalar($value) || $value === null) {
            $safeContext[$key] = $value;
            continue;
        }

        $safeContext[$key] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    $contextJson = $safeContext ? json_encode($safeContext, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '{}';
    $riadokchyby = sprintf("[%s] %s: %s %s\n", date('Y-m-d H:i:s'), $uroven, $sprava, $contextJson);

    return file_put_contents($logFile, $riadokchyby, FILE_APPEND | LOCK_EX) !== false;
}

function app_render_friendly_error($publicMessage = 'Prepacte, nieco sa pokazilo. Skuste to prosim znova o chvilu.') {
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=UTF-8');
    }

    //error message vrati stranku s chybou 500
    $errorMessage = $publicMessage;
    $errorPagePath = dirname(__DIR__) . '/views/partials/error500.php';

    if (is_file($errorPagePath)) {
        include $errorPagePath;
        exit;
    }

    echo '<h1>Chyba aplikacie</h1>';
    echo '<p>' . htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') . '</p>';
    exit;
}

function app_register_error_handlers() {
    set_error_handler(function ($typ_chyby, $sprava, $suborchyby, $riadokchyby) {
        if (!(error_reporting() & $typ_chyby)) {
            return false;
        }

        app_log('warning', 'PHP warning/notice', [
            'severity' => $typ_chyby,
            'message' => $sprava,
            'file' => $suborchyby,
            'line' => $riadokchyby,
            'uri' => $_SERVER['REQUEST_URI'] ?? '',
        ]);

        return false;
    });

    set_exception_handler(function (Throwable $e) {
        app_log('error', 'Uncaught exception', [
            'type' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'uri' => $_SERVER['REQUEST_URI'] ?? '',
        ]);

        app_render_friendly_error();
    });

    register_shutdown_function(function () {
        $lastError = error_get_last();
        if ($lastError === null) {
            return;
        }

        $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
        if (!in_array($lastError['type'], $fatalTypes, true)) {
            return;
        }

        app_log('critical', 'Fatal PHP error', [
            'type' => $lastError['type'],
            'message' => $lastError['message'] ?? '',
            'file' => $lastError['file'] ?? '',
            'line' => $lastError['line'] ?? 0,
            'uri' => $_SERVER['REQUEST_URI'] ?? '',
        ]);

        app_render_friendly_error('Aplikacia narazila na kriticku chybu. Skuste to prosim neskor.');
    });
}



/*


<?php
baseUrl();           // '/Red_Ghost'
route('home');       // '/Red_Ghost/home'
route('/login');     // '/Red_Ghost/login'


*/