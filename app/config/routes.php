<?php

use App\Controllers\HomeController;
use App\Controllers\UploadController;
use App\Controllers\AuthController;
use App\Controllers\AdminController;


//////////////////// Routes accessible par tous le monde ////////////////////
// Routes login
$app->get('/login', AuthController::class. ':getLogin')->setName('login');
$app->post('/login', AuthController::class. ':postLogin');


////////////// Routes accessibles par les utilisateurs uniquement /////////////
// Middleware pour checker la session utilisateur
$app->group('', function () {

  // Route logout
  $this->get('/logout', AuthController::class. ':getLogout')->setName('logout');

  // Route répertoire personnel
  $this->get('/', HomeController::class. ':getHome')->setName('home');

  // Routes profil
  $this->get('/profil', HomeController::class. ':getProfil')->setName('profil');
  $this->post('/profil', HomeController::class. ':postProfil');

  // Routes upload
  $this->get('/upload', UploadController::class. ':getUpload')->setName('upload');
  $this->post('/upload', UploadController::class. ':postUpload');
  $this->get('/upload_progress', UploadController::class. ':getUploadProgress')->setName('upload_progress');

})->add(new App\Middlewares\AuthMiddleware($container->view->getEnvironment(), $container));


////////////// Routes accessibles par le compte administrateur uniquement /////////////
// Middlewares pour checker la session utilisateur et droit admin
$app->group('', function () {

  // Route logs admin
  $this->get('/admin', AdminController::class. ':getLog')->setName('admin');

  // Routes settings admin
  $this->get('/admin/settings', AdminController::class. ':getSettings')->setName('settings');
  $this->post('/admin/settings', AdminController::class. ':postSettings');

  // Routes users admin
  $this->get('/admin/users', AdminController::class. ':getUsers')->setName('users');
  $this->post('/admin/users', AdminController::class. ':postUsers');
  $this->get('/admin/add_user', AdminController::class. ':getAddUser')->setName('add_user');
  $this->post('/admin/add_user', AdminController::class. ':postAddUser');

  $this->get('/admin/edit_user/{id}', AdminController::class. ':getEditUser')->setName('edit_user');
  $this->post('/admin/edit_user/{id}', AdminController::class. ':postEditUser');
  $this->get('/admin/del_user/{id}', AdminController::class. ':getDelUser')->setName('del_user');

  // Routes directory admin
  $this->get('/admin/directory', AdminController::class. ':getDirectory')->setName('directory');

  $this->get('/admin/add_directory', AdminController::class. ':getAddDirectory')->setName('add_directory');
  $this->post('/admin/add_directory', AdminController::class. ':postAddDirectory');

  $this->get('/admin/edit_directory', AdminController::class. ':getEditDirectory')->setName('edit_directory');
  $this->post('/admin/edit_directory', AdminController::class. ':postEditDirectory');

})
->add(new App\Middlewares\AuthMiddleware($container->view->getEnvironment(), $container))
->add(new App\Middlewares\AdminMiddleware($container));
