<?php
use Cloudinary\Configuration\Configuration;
Use Core\DB;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__."/..");
$dotenv->load();

require (__DIR__."/DB.php");
DB::setConnection("localhost","real-estate", "root", "");

// configure an instance via a JSON object
Configuration::instance([
    'cloud' => [
      'cloud_name' => $_ENV["CLOUDINARY_CLOUD_NAME"],
      'api_key'  => $_ENV["CLOUDINARY_API_KEY"],
      'api_secret' => $_ENV["CLOUDINARY_API_SECRET"],
    'url' => [
      'secure' => true]]]);


require (__DIR__."/../routes.php");


