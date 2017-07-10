<?php

use App\Controllers\HomeController;
use App\Controllers\UploadController;

$app->get('/', HomeController::class. ':getHome')->setName('home');
$app->post('/', HomeController::class. ':postHome');
$app->get('/upload', UploadController::class. ':getUpload')->setName('upload');
$app->post('/upload', UploadController::class. ':postUpload');
$app->get('/upload_progress', UploadController::class. ':getUploadProgress')->setName('upload_progress');
