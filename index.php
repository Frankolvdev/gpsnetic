<?php

// Valid PHP Version?
$minPHPVersion = '7.2';
if (phpversion() < $minPHPVersion) {
    die("Your PHP version must be {$minPHPVersion} or higher to run CodeIgniter. Current version: " . phpversion());
}
unset($minPHPVersion);

// --- CLAVE: FCPATH ahora es la RAÍZ del proyecto ---
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

// Paths.php está en app/Config/Paths.php → desde la raíz
$pathsPath = realpath(FCPATH . 'app/Config/Paths.php');

if (!is_file($pathsPath)) {
    die("No se encontró app/Config/Paths.php. Verifica la estructura.");
}

/*
 *---------------------------------------------------------------
 * FIX PARA NGINX SIN TOCAR CONFIGURACIÓN
 *---------------------------------------------------------------
 * Nginx NO pasa PATH_INFO por defecto.
 * CodeIgniter necesita PATH_INFO para rutas como /webhook.
 * Este fix reconstruye PATH_INFO basándose en REQUEST_URI.
 */
if (!isset($_SERVER['PATH_INFO']) || empty($_SERVER['PATH_INFO'])) {
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    // Quitar la carpeta base del proyecto si existe
    $baseFolder = '/gpsnetic_notifications';
    if (strpos($uri, $baseFolder) === 0) {
        $uri = substr($uri, strlen($baseFolder));
    }

    // Evitar que quede vacío → poner "/"
    $_SERVER['PATH_INFO'] = $uri ?: '/';
}

/*
 *---------------------------------------------------------------
 * BOOTSTRAP
 *---------------------------------------------------------------
 */
chdir(__DIR__); // Asegurarse de estar en la raíz

require $pathsPath;
$paths = new Config\Paths();

// Cargar bootstrap del sistema
$app = require rtrim($paths->systemDirectory, '/ ') . '/bootstrap.php';

/*
 *---------------------------------------------------------------
 * RUN
 *---------------------------------------------------------------
 */
$app->run();
