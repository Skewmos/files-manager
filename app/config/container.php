<?php

$container = $app->getContainer();
$container['container'] = $app->getContainer();

// Twig
$container['view'] = function ($container) use ($env) {
  $pathView = dirname(dirname(__DIR__));

  if(file_exists($env)){
    $cache = $pathView.'/cache';
  }else{
    $cache = false;
  }
  $view = new \Slim\Views\Twig($pathView.'/app/views', [
    'cache' => $cache
  ]);

  $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
  $view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));

  return $view;
};

if(file_exists($env)){
  // Medoo
  $container['medoo'] = function () {
    $medoo = new Medoo\Medoo([
      'database_type' => getenv('DBP_TYPE'),
      'database_name' => getenv('DBP_NAME'),
      'server' => getenv('DBP_SERVER'),
      'username' => getenv('DBP_USER'),
      'password' => getenv('DBP_PWD')
    ]);
    return $medoo;
  };
}

//Csrf
$container['csrf'] = function () {
    return new \Slim\Csrf\Guard;
};
