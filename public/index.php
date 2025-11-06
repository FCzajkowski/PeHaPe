<?php

use App\Kernel;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;

require dirname(__DIR__).'/vendor/autoload_runtime.php';

// Pobranie URL
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';

// Jeśli użytkownik wchodzi na stronę główną "/"
if ($requestUri === '/' || $requestUri === '/index.php') {
    // Wczytanie Twojego starego controller.php
    require __DIR__ . '/controller.php';
    exit;
}

// Dla reszty żądań – standardowy Symfony Kernel
return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};



//
//<?php
//
//use App\Kernel;
//
//require_once dirname(__DIR__).'/vendor/autoload_runtime.php';
//
//return function (array $context) {
//    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
//};
