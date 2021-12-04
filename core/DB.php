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

    public function bindAllParams($statement, $request)
    {
        //bind params
        foreach ($request as $bodyParam => $value)
        {
            $bodyValue = $value;
            $statement->bindValue(":{$bodyParam}", $bodyValue);
        }

        return $statement;
    }

    public function getQueryParams($body, $allowedFields)
    {
        $queryParams = [];

        foreach($body as $bodyParam => $value)
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