<?php
 class DB 
 {
     //in later modifications, call the variables from .env file
     public function connect($dbname, $user, $password)
     {
         $dsn = "mysql:host=localhost;"."dbname={$dbname}";

         try 
         {
            $con =  new PDO ($dsn, $user, $password);
            return $con;

         } catch(PDOException $exception)
         {
            exit ($exception->getMessage());
         }
        
    }

 }