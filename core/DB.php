<?php
namespace Core;

use PDO;
use Laminas\Diactoros\Response\JsonResponse;

class DB
{
    static $con;


    /**
     * Creates Database connection.
     * 
     * @param string $host
     * @param string $dbname
     * @param string $user
     * @param string $password
     * 
     * @return object
     * 
     */

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

    
    /**
     * Get connection object
     * 
     * @return object
     * 
     */


    public static function getConnection()
    {
        return self::$con;
    }

    
    /**
     * Creates PHP PDO "Bind Param Statement" for all fields in an SQL statement.
     * 
     * @param string $statement
     * @param array $data
     * @param array $allowedFields
     * 
     * @return object
     * 
     */


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

    /**
     * Genereate SQL query parameters for all fields in an SQL statement (e.g  "book=:book, ISBN=:ISBN ...").
     * 
     * @param array $data
     * @param array $allowedFields
     * 
     * @return object
     * 
     */

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

    /**
     * Finds the number of rows that exists in database table where a field equals a certain value.
     * 
     * @param string $table
     * @param string $field
     * @param string $fieldValue
     * @return object
     * 
     */
    public static function numOfRows($field, $table, $fieldValue)
    {
        $statement = self::$con->prepare("SELECT COUNT(*) FROM `{$table}` WHERE {$field}=:{$field} ");        
        $statement->bindValue(":{$field}", $fieldValue);
        $statement->execute();
        $field_count = $statement->fetchColumn();

        return $field_count;
    }

}