<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Central Routes (Super Admin Panel)
|--------------------------------------------------------------------------
|
| Estas rutas corren SOLO en dominios centrales (sas.dev-app.cl, localhost).
| Se registran desde TenancyServiceProvider con Route::domain().
|
*/

Route::get('/', function () {
    return view('central.landing');
})->name('central.landing');

// TODO: Fase 1 - Login super admin, CRUD tenants/bodies
