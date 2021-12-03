<?php
namespace API\V1\Controllers;
use Core\DB;

class ListingController
{ private $con;
    public function __construct()
    {
        $db = new DB();
        $this->con = $db->connect("real-estate", "root", "");
    }
    public function index()
    {
        return json_encode(["data" => "All listinvfdfgs"]);
    }

    public function show()
    {
        return json_encode(["data" => "Show single listing"]);
    }

    public function create()
    {
        return json_encode(["data" => "Listing created"]);
    }

    public function update()
    {
        return json_encode(["data" => "Listing updated"]);
    }

    public function destroy()
    {
        return json_encode(["data" => "Listing deleted"]);
    }
}