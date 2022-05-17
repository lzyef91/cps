<?php

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Dcat\Admin\Admin;

Admin::routes();

Route::group([
    'prefix'     => config('admin.route.prefix'),
    'namespace'  => config('admin.route.namespace'),
    'middleware' => config('admin.route.middleware'),
], function (Router $router) {

    $router->redirect('/', '/admin/youzan/shops');
    $router->resource('youzan/shops', 'YouzanShopController');
    $router->get('exports', 'ExportController@index');

});
