<?php

use App\Controllers\HomeController;
use App\Controllers\UploadController;
use App\Controllers\LoginController;

$app->get('/', HomeController::class. ':getHome')->setName('home');
$app->post('/', HomeController::class. ':postHome');

// Routes login
$app->get('/login', LoginController::class. ':getLogin')->setName('login');
$app->post('/login', LoginController::class. ':postLogin');
$app->get('/logout', LoginController::class. ':getLogout')->setName('logout');

// Routes upload
$app->get('/upload', UploadController::class. ':getUpload')->setName('upload');
$app->post('/upload', UploadController::class. ':postUpload');
$app->get('/upload_progress', UploadController::class. ':getUploadProgress')->setName('upload_progress');
