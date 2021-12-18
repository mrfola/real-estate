<?php
namespace API\V1\Controllers;

use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequest;
use Firebase\JWT\JWT;
use Valitron\Validator;
use API\V1\Controllers\AuthController;
use API\V1\Models\Listing;


class ListingController
{ 
    
    public function index(ServerRequest $request)
    {
        $listing = new Listing();
        return $listing->getAllListings();      
    }

    public function show($id)
    {
        $listing = new Listing();
        return $listing->getListing($id);
    }

    public function create(ServerRequest $request)
    {
        $data = $request->getParsedBody();         //get request content
        $data["user_id"] = AuthController::get_user();

        //validate
        $validator = new Validator($data);
        $validator->rule('required',  ["user_id", "name", "description", "pictures", "details", "location"]);

        if($validator->validate())
        {
            $listing = new Listing();
            return $listing->createListing($data);

        } else
        {
            return new JsonResponse(["errors" => $validator->errors()], 504);
        }
    }

    public function update(ServerRequest $request, $id)
    {
        $data =  $request->getQueryParams();//get request data
        
        $listing = new Listing();
        return $listing->updateListing($data, $id);
      
    }

    public function destroy($id)
    {
       $listing = new Listing();
       return $listing->deleteListing($id);
    }


}