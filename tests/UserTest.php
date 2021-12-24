<?php

use \PHPUnit\Framework\TestCase;
use API\V1\Controllers\User;

class SampleTest extends TestCase
{

    // public function setUp()
    // {
        
    // }

    // public function tearDown()
    // {
    //     # code...
    // }

    public function test_user_returns_correct_data_on_create()
    {
        $user = new User();
        $user_data = 
        [
            "name" => "John Doe",
            "email" => "johndoe@gmail.com",
            "password" => "password"
        ];

        $created_user = $user->create($user_data);
        $this->assertTrue(array_key_exists("id", [$created_user]));
        $this->assertTrue(array_key_exists("name", [$created_user]));
        $this->assertTrue(array_key_exists("password", [$created_user]));
        $this->assertTrue(array_key_exists("created_at", [$created_user]));
        $this->assertTrue(array_key_exists("updated_at", [$created_user]));

        $this->assertEquals("John Doe", ts("updated_at", [$created_user["name"]]));
        $this->assertEquals("johndoe@gmail.com", ts("updated_at", [$created_user["email"]]));

    }


    public function test_user_password_is_hashed_before_storing_to_database(Type $var = null)
    {
        # code...
    }
   
    // test_that_incomplete_data_throws_exception
    //test_that_json_is_returned_after_creating_user
    //test_that_i_can't_create_user_with_existing_email
    //test_that_user_email_must_be_an_actual_email
    //test_that_password_is_not_returned_with_request
}