<?php

echo dirname(dirname(__DIR__));

require dirname(dirname(__DIR__)).'/vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(dirname(dirname(__DIR__)));
$dotenv->load();

return [
  'paths' => [
    'migrations' => dirname(dirname(__DIR__)).'/app/database/migrations',
    'seeds' => dirname(dirname(__DIR__)).'/app/database/seeds'
  ],
  'environments' => [
    'default_database' => 'production',
    'production' => [
      'adapter' => getenv('DBP_TYPE'),
      'host' => getenv('DBP_SERVER'),
      'name' => getenv('DBP_NAME'),
      'user' => getenv('DBP_USER'),
      'pass' => getenv('DBP_PWD')
    ]
  ]
];
