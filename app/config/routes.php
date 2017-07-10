<?php

use App\Controllers\HomeController;
use App\Controllers\UploadController;
use App\Controllers\AuthController;


//////////////////// Routes accessible par tous le monde ////////////////////
// Routes login
$app->get('/login', AuthController::class. ':getLogin')->setName('login');
$app->post('/login', AuthController::class. ':postLogin');


////////////// Routes accessible par les utilisateur uniquement /////////////

// Middleware pour checker les sessions
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

})->add(new App\Middlewares\SessionMiddleware($container));
