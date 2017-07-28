<?php

use App\Controllers\HomeController;
use App\Controllers\UploadDownloadController;
use App\Controllers\AuthController;
use App\Controllers\AdminController;
use App\Controllers\SystemController;


//////////////////// Routes accessible par tous le monde ////////////////////
// Routes login
$app->get('/login', AuthController::class. ':getLogin')->setName('login');
$app->post('/login', AuthController::class. ':postLogin');

// Routes install
$app->get('/install', SystemController::class. ':getInstall')->setName('install');
$app->post('/install', SystemController::class. ':postInstall');

////////////// Routes accessibles par les utilisateurs uniquement /////////////
// Middleware pour checker la session utilisateur
$app->group('', function () {

  // Route logout
  $this->get('/logout', AuthController::class. ':getLogout')->setName('logout');

  // Routes répertoire personnel
  $this->get('/', HomeController::class. ':getHome')->setName('home');
  $this->get('/delete/{id}', HomeController::class. ':getDelFile')->setName('del_file');

  // Routes des répertoires partagés
  $this->get('/dir', HomeController::class. ':getDirectory')->setName('dir');

  // Routes profil
  $this->get('/profil', HomeController::class. ':getProfil')->setName('profil');
  $this->post('/profil', HomeController::class. ':postProfil');

  // Routes upload
  $this->get('/upload', UploadDownloadController::class. ':getUpload')->setName('upload');
  $this->post('/upload', UploadDownloadController::class. ':postUpload');

  // Routes de téléchargement/stream
  $this->get('/download/user/{user}/{file}', UploadDownloadController::class. ':getDownloadUser')->setName('download_user');
  $this->get('/download/dir/{dir}/{file}', UploadDownloadController::class. ':getDownloadDir')->setName('download_dir');

  $this->get('/stream/user/{user}/{file}', UploadDownloadController::class. ':getSteam')->setName('stream');

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
  $this->get('/admin/dir', AdminController::class. ':getDirectory')->setName('admin_dir');

  $this->get('/admin/add_dir', AdminController::class. ':getAddDirectory')->setName('add_dir');
  $this->post('/admin/add_dir', AdminController::class. ':postAddDirectory');

  $this->get('/admin/edit_dir', AdminController::class. ':getEditDirectory')->setName('edit_dir');
  $this->post('/admin/edit_dir', AdminController::class. ':postEditDirectory');

})
->add(new App\Middlewares\AuthMiddleware($container->view->getEnvironment(), $container))
->add(new App\Middlewares\AdminMiddleware($container));
