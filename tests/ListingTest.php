<?php
namespace Tests;
use Core\DB;
use \PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

class ListingTest extends  TestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = new Client(["base_uri" => $_ENV["TEST_BASE_URL"]]);
    }

    protected function tearDown(): void
    {
        $this->client = "";

        $statement = DB::$con->prepare("TRUNCATE TABLE `Listings`");
        $statement = DB::$con->prepare("TRUNCATE TABLE `Users`");
        $statement->execute();
    }

    public function create_user(array $data)
    {
        $create_user = $this->client->post('/users', ['form_params' => $data]);
        $user = json_decode($create_user->getBody()->getContents(), true);
        return $user;
    }

    public function login()
    {
        //create user
        $data = 
        [
            "name" => "John Doe",
            "email" => "johndoe@gmail.com",
            "phonenumber" => "0809009090",
            "password" => "password"
        ];

        $this->create_user($data);

        $credentials = ["email" => $data["email"], "password" => $data["password"]];
        $user = $this->client->post('/login', ['form_params' => $credentials]);
        $user = json_decode($user->getBody()->getContents(), true);
        return $user["token"];
    }      

    public function test_that_you_need_to_login_to_create_listing()
    {
        $data = 
        [
         "name" => "New listing",
         "description" => "It's a really beautiful home",
         "images" => "C:\Users\Mr Fola\Desktop\All\Memes\man jacket.png,C:\Users\Mr Fola\Desktop\All\Memes\child-gun.png,C:\Users\Mr Fola\Desktop\All\Memes\baby-meme.jpg",
         "details" => "It is a 4 story building with 4 windows",
         "location" => "Lagos, Nigeria"
        ];

        $listing = $this->client->post("/listings", ["http_errors" => false, "form_params" => $data]);
        $this->assertEquals("401", $listing->getStatusCode());
    }

    public function test_that_a_wrong_image_url_throws_error()
    {
        $token = $this->login();

        $data = 
        [
         "name" => "New listing",
         "description" => "It's a really beautiful home",
         "images" => "test.jpg",
         "details" => "It is a 4 story building with 4 windows",
         "location" => "Lagos, Nigeria"
        ];

        $listing = $this->client->post("/listings",[
            "http_errors" => true,
            "form_params" => $data,
            'headers' => ["Authorization" => "Bearer ".$token]]);

        var_dump($listing->getBody()->getContents());
        $this->assertEquals("401", $listing->getStatusCode());
        
    } 
    //test_that_images_upload_to_cloudinary_on_create
    //test_that_you_can_create_listing
    //test_that_you_get_an_error_when_creating_listing_with_incomplete_fields
    //test_that_listing_returns_correct_data_on_create
    //test_that_you_get_json_when_you_get_listing
    //test_that_you_can_get_listing
    //test_that_you_need_to_login_to_update_listing
    //test_that_you_can_update_listing
    //test_that_you_need_to_login_to_delete_listing
    //test_that_you_can_delete_listing
    //test_that_you_can_only_update_your_own_user
    //test_that_you_can_only_delete_your_own_user

}