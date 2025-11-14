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