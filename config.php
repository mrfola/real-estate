<?php
use Cloudinary\Configuration\Configuration;
Use Core\DB;

require (__DIR__."/core/DB.php");


//load .ENV Package
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

//Load Database
if($_ENV['APP_ENV'] == 'local')
{
  DB::setConnection($_ENV['DB_HOST'],$_ENV['DB_DATABASE'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);
}

if($_ENV['APP_ENV'] == 'testing')
{
  DB::setConnection($_ENV['TEST_DB_HOST'],$_ENV['TEST_DB_DATABASE'], $_ENV['TEST_DB_USERNAME'], $_ENV['TEST_DB_PASSWORD']);
}

// configure an instance via a JSON object
Configuration::instance([
    'cloud' => [
      'cloud_name' => $_ENV["CLOUDINARY_CLOUD_NAME"],
      'api_key'  => $_ENV["CLOUDINARY_API_KEY"],
      'api_secret' => $_ENV["CLOUDINARY_API_SECRET"],
    'url' => [
      'secure' => true]]]);




