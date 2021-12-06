<?php
namespace Core;

use PDO;
use Laminas\Diactoros\Response\JsonResponse;

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

    public function bindAllParams($statement, $data, $allowedFields)
    {
        //bind params
        foreach ($data as $bodyParam => $value)
        {
            if(in_array($bodyParam, $allowedFields))
            {
                $statement->bindValue(":{$bodyParam}", $value);
            }else
            {
                //IMPROVEMENTS: 
                //Make this send out a JSON response with response code of bad request
                exit("Not allowed");
            }
        }

        return $statement;
    }

    public function getQueryParams($data, $allowedFields)
    {
        $queryParams = [];

        foreach($data as $bodyParam => $value)
        {
            if(in_array($bodyParam, $allowedFields))
            {
                $queryParams[] = $bodyParam."=:".$bodyParam;
            }else
            {
                //IMPROVEMENTS: 
                //Make this send out a JSON response with response code of bad request
                exit("Not allowed");
               //exit(new JsonResponse(["error" => "Not allowed"]));
            }
        }

        $queryParams = implode(",", $queryParams);
        return $queryParams;
    }

}