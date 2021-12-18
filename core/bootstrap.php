<?php
require (__DIR__."/DB.php");

Use Core\DB;
DB::setConnection("localhost","real-estate", "root", "");

require (__DIR__."/../routes.php");


