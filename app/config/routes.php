<?php

use App\Controllers\HomeController;
use App\Controllers\UploadController;
use App\Controllers\AuthController;
use App\Controllers\AdminController;


//////////////////// Routes accessible par tous le monde ////////////////////
// Routes login
$app->get('/login', AuthController::class. ':getLogin')->setName('login');
$app->post('/login', AuthController::class. ':postLogin');


////////////// Routes accessible par les utilisateur uniquement /////////////
// Middleware pour checker la session utilisateur
$app->group('', function () {

  // Route logout
  $this->get('/logout', AuthController::class. ':getLogout')->setName('logout');

  // Routes répertoire personnel
  $this->get('/', HomeController::class. ':getHome')->setName('home');
  $this->post('/', HomeController::class. ':postHome');

  // Routes upload
  $this->get('/upload', UploadController::class. ':getUpload')->setName('upload');
  $this->post('/upload', UploadController::class. ':postUpload');
  $this->get('/upload_progress', UploadController::class. ':getUploadProgress')->setName('upload_progress');

})->add(new App\Middlewares\AuthMiddleware($container->view->getEnvironment(), $container));


////////////// Routes accessible par le compte administrateur uniquement /////////////
// Middleware pour checker la session et droit admin
$app->group('', function () {

  // Route home admin
  $this->get('/admin', AdminController::class. ':getHome')->setName('admin');

  // Route settings admin
  $this->get('/admin/settings', AdminController::class. ':getSettings')->setName('settings');
  $this->post('/admin/settings', AdminController::class. ':postSettings');

})
->add(new App\Middlewares\AuthMiddleware($container->view->getEnvironment(), $container))
->add(new App\Middlewares\AdminMiddleware($container));
