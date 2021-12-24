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

    //test_that_json_is_returned_after_creating_user
    //test_that_i_can't_create_user_with_existing_email
    //test_that_user_email_must_be_an_actual_email
    //test_that_password_is_not_returned_with_request
}