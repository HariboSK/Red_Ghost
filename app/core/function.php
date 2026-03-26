<?php

function app_base_path() {
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $scriptDir = str_replace('\\', '/', dirname($scriptName));
    $scriptDir = rtrim($scriptDir, '/');

    if ($scriptDir === '.' || $scriptDir === '/' || $scriptDir === '\\') {
        return '';
    }

    return $scriptDir;
}

function route($path) {
    if (preg_match('#^(https?:)?//#i', (string) $path) === 1) {
        return htmlspecialchars((string) $path, ENT_QUOTES, 'UTF-8');
    }

    $basePath = app_base_path();
    $normalizedPath = '/' . ltrim((string) $path, '/');

    return htmlspecialchars($basePath . $normalizedPath, ENT_QUOTES, 'UTF-8');
}

function baseUrl() {
    return app_base_path();
}
