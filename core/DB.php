<?php
namespace Core;

use PDO;
use Laminas\Diactoros\Response\JsonResponse;

class DB
{
    static $con;
    //in later modifications, call the variables from .env file
    public static function setConnection($host, $dbname, $user, $password)
    {
        $dsn = "mysql:host={$host};"."dbname={$dbname}";

        try 
        {
        $con =  new PDO ($dsn, $user, $password);
        self::$con = $con;
        return self::$con;

        } catch(PDOException $exception)
        {
        exit ($exception->getMessage());
        }
    
    }   

    public static function getConnection()
    {
        return self::$con;
    }

    public static function bindAllParams($statement, $data, $allowedFields)
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

    public static function getQueryParams($data, $allowedFields)
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

    public static function isUnique($field, $table, $fieldValue)
    {
        $statement = self::$con->prepare("SELECT COUNT(*) FROM `{$table}` WHERE {$field}=:{$field} ");        
        $statement->bindValue(":{$field}", $fieldValue);
        $statement->execute();
        $field_count = $statement->fetchColumn();

        if($field_count <= 0)
        {
            return true;
        }else
        {
            return false;
        }
    }

}