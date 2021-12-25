<?php
use \PHPUnit\Framework\TestCase;
use Core\DB;
use GuzzleHttp\Client;
use API\V1\Exceptions\UsedEmailException;

class UserTest extends TestCase
{
    private $client;

    protected function setUp():void
    {
        $this->client = new Client(["base_uri" => $_ENV["TEST_BASE_URL"]]);
    }

    protected function tearDown():void
    {
        $this->client = "";

        $statement = DB::$con->prepare("TRUNCATE TABLE `users`");
        $statement->execute();
    }
    
    public function create_user(array $data)
    {
        $create_user = $this->client->post('/users', ['form_params' => $data]);
        $user = json_decode($create_user->getBody()->getContents(), true);
        return $user;
    }

    public function login(array $credentials)
    {
        $user = $this->client->post('/login', ['form_params' => $credentials]);
        $user = json_decode($user->getBody()->getContents(), true);
        return $user["token"];
    }      

    public function test_user_returns_correct_data_on_create()
    { 
        $data = 
        [
            "name" => "John Doe",
            "email" => "johndoe@gmail.com",
            "phonenumber" => "0809009090",
            "password" => "password"
        ];

        $user = $this->client->post('/users', ['form_params' => $data]);

        $created_user = json_decode($user->getBody()->getContents(), true);

        $this->assertTrue(array_key_exists("id", $created_user));
        $this->assertTrue(array_key_exists("name", $created_user));
        $this->assertTrue(array_key_exists("created_at", $created_user));
        $this->assertTrue(array_key_exists("updated_at", $created_user));

        $this->assertEquals("John Doe", ($created_user["name"]));
        $this->assertEquals("johndoe@gmail.com", ($created_user["email"]));
        $this->assertEquals("200", $user->getStatusCode());

    }


    // public function test_user_password_is_hashed_before_storing_to_database()
    // {
    //     $data = 
    //     [
    //         "name" => "John Doe",
    //         "email" => "johndoe@gmail.com",
    //         "phonenumber" => "0809009090",
    //         "password" => "password"
    //     ];

    //     $user = $this->client->request('POST', 'http://localhost:8000/users', ['form_params' => $data]);

    //     $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
    // }

    /**
     * @expectedException PHPUnit\Framework\Error\Error
     * 
    */

    public function test_that_incomplete_data_throws_error()
    {
        $data = 
            [
                "name" => "John Doe",
                "email" => "johndoe@gmail.com",
                "password" => "password"
            ];

            $user = $this->client->post('/users', ['http_errors' => false, 'form_params' => $data]);
            $this->assertEquals("400", $user->getStatusCode());
    }

    public function test_that_json_is_returned_after_creating_user()
    {
        $data = 
        [
            "name" => "John Doe",
            "email" => "johndoe@gmail.com",
            "phonenumber" => "0809009090",
            "password" => "password"
        ];

        $user = $this->client->post('/users', ['form_params' => $data]);
        $this->assertJson($user->getBody()->getContents());
    }

    public function test_that_you_cant_create_user_with_existing_email()
    {
        $data_1 = 
        [
            "name" => "John Doe",
            "email" => "johndoe@gmail.com",
            "phonenumber" => "0809009090",
            "password" => "password"
        ];

        $user_1 = $this->client->post('/users', ['form_params' => $data_1]);

        $data_2 = 
        [
            "name" => "John Doe2",
            "email" => "johndoe@gmail.com",
            "phonenumber" => "08099648902",
            "password" => "password2"
        ];

        $user_2 = $this->client->post('/users', ['http_errors' => false, 'form_params' => $data_2]);
        $this->assertEquals("405", $user_2->getStatusCode());

    }
    //this test doesn't in anyway confirm that it is the email causing the error. 
    //It confirms the status code but there should be a way to confirm that it is the email causing the error. 
    //Like reading the error message and confirming it matches the expected error message but the issue is that the error message can be changed.
    //And I would not like to change my test everytime an error message changes from the controller
    public function test_that_user_email_must_be_an_actual_email()
    {
        $data = 
        [
            "name" => "John Doe",
            "email" => "johndoegmail.com", //bad email
            "phonenumber" => "0809009090",
            "password" => "password"
        ];

        $user = $this->client->post('/users', ['http_errors'=>false, 'form_params' => $data]);
        $this->assertEquals("400", $user->getStatusCode());
    }

    public function test_that_password_is_not_returned_with_request()
    {
        $data = 
        [
            "name" => "John Doe",
            "email" => "johndoe@gmail.com",
            "phonenumber" => "0809009090",
            "password" => "password"
        ]; 

        $user = $this->client->post('/users', ['form_params' => $data]);
        $this->assertFalse(array_key_exists("password", json_decode($user->getBody()->getContents(), true)));
    }

    public function test_that_you_need_to_login_before_getting_user()
    {
        //create user
        $data = 
        [
            "name" => "John Doe",
            "email" => "johndoe@gmail.com",
            "phonenumber" => "0809009090",
            "password" => "password"
        ]; 

        $create_user = $this->client->post('/users', ['form_params' => $data]);

        //get user
        $user_id = json_decode($create_user->getBody()->getContents(), true)["id"];//user id

        $get_user = $this->client->get("/users/".$user_id,  ['http_errors' => false]);
        $user = json_decode($get_user->getBody()->getContents(), true);

        $this->assertEquals("401", $get_user->getStatusCode());
    }

    public function test_that_we_can_get_user()
    {
        //create user
        $data = 
        [
            "name" => "John Doe",
            "email" => "johndoe@gmail.com",
            "phonenumber" => "0809009090",
            "password" => "password"
        ]; 

        $created_user = $this->create_user($data);
        
        //login
        $token = $this->login(["email" => $data["email"], "password" => $data["password"]]);

        //get user  
        $header = 
        [
            'headers' => ["Authorization" => "Bearer ".$token]
        ];

        $get_user = $this->client->get("/users/".$created_user["id"], $header);

        $user = json_decode($get_user->getBody()->getContents(), true);

        $this->assertTrue(array_key_exists("id", $user));
        $this->assertTrue(array_key_exists("name", $user));
        $this->assertTrue(array_key_exists("created_at", $user));
        $this->assertTrue(array_key_exists("updated_at", $user));

        $this->assertEquals("John Doe", ($user["name"]));
        $this->assertEquals("johndoe@gmail.com", ($user["email"]));
        $this->assertEquals("0809009090", ($user["phonenumber"]));
        $this->assertEquals("200", $get_user->getStatusCode());

    }

    //test_that_you_can_only_update_your_own_self
    //test_that_you_can_update_user
}