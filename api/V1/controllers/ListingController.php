<?php
namespace API\V1\Controllers;

use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequest;
use Firebase\JWT\JWT;
use Valitron\Validator;
use API\V1\Controllers\AuthController;
use API\V1\Models\Listing;
use Cloudinary\Api\Upload\UploadApi;

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
        $data["user_id"] = AuthController::getUserId();

        //validate
        $validator = new Validator($data);
        $validator->rule('required',  ["user_id", "name", "description", "images", "details", "location"]);
        
        if($validator->validate())
        {
            //save images to cloudinary
            $images = explode(",", $data["images"]);  //convert images string to array
            $uploaded_images = [];
            foreach ($images as $image_url)
            {
                $uploaded_image = (new UploadApi())->upload("{$image_url}");
                $uploaded_images[] = $uploaded_image["url"];
            }

            $data["images"] = implode(",", $uploaded_images); //convert images array back to string
 
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