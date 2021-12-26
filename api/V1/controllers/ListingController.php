<?php
namespace API\V1\Controllers;

use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequest;
use Firebase\JWT\JWT;
use Valitron\Validator;
use API\V1\Controllers\AuthController;
use API\V1\Models\Listing;
use Cloudinary\Api\Upload\UploadApi;
use Exception;
use API\V1\Exceptions\EmptyQueryException;
use API\V1\Exceptions\NotFoundException;
use API\V1\Exceptions\UnauthorizedUserException;

class ListingController
{ 
    /**
     * Gets all Listings.
     * 
     * @param array $request
     * @return object 
     * 
     */

    public function index(ServerRequest $request)
    {
        $listing = new Listing();
        return $listing->getAllListings();      
    }

    /**
     * Gets single Listing.
     * 
     * @param int $id
     * @return object 
     * 
     */

    public function show($id)
    {
        $listing = new Listing();
        return $listing->getListing($id);
    }


    /**
     * Create Listing.
     * 
     * This method gets data from the request, extracts the listing image links and converts it into an array. 
     * It then stores these images to cloudinary storage, after which it gets a new url and then updates the request to replace the old image urls with the new image urls
     * It then saves this new data to the databse
     * 
     * @param array $request
     * @return object 
     * 
     */

    public function create(ServerRequest $request)
    {
        $data = $request->getParsedBody();//get request content
        $data["owner_id"] = AuthController::getUserId();

        //validate
        $validator = new Validator($data);
        $validator->rule('required',  ["owner_id", "name", "description", "images", "details", "location"]);
        
        if($validator->validate())
        {
            //save images to cloudinary
            $images = explode(",", $data["images"]);  //convert images string to array
            $uploaded_images = [];
            foreach ($images as $image_url)
            {
                try
                {
                    $uploaded_image = (new UploadApi())->upload("{$image_url}");
                }
                catch (Exception $error)
                {
                    //check if error message is due to bad url
                    if (substr($error->getMessage(), 0, 5) == "fopen")
                    {
                        return new JsonResponse(["errors" => "Could not open images. Please try again"], 400);

                    }else
                    {
                        return new JsonResponse(["errors" => "Something went wrong. Please try again"], 500);
                    }
                }

                $uploaded_images[] = $uploaded_image["url"];
            }

            $data["images"] = implode(",", $uploaded_images); //convert images array back to string
 
            $listing = new Listing();
            return $listing->createListing($data);

        } else
        {
            return new JsonResponse(["errors" => $validator->errors()], 400);
        }
    }


    /**
     * Update Listing.
     * 
     * @param array $request
     * @return object 
     * 
     */
    public function update(ServerRequest $request, $id)
    {
        $data =  $request->getQueryParams();//get request data
        $data["id"] = $id;
        
        $listing = new Listing();
        try
        {
        $updated_listing = $listing->updateListing($data);
        }catch (EmptyQueryException $error)
        {
            return new JsonResponse(["errors" => $error->getMessage()], 400);  

        }
        catch (NotFoundException $error)
        {
            return new JsonResponse(["errors" => $error->getMessage()], 404);  

        }catch (UnauthorizedUserException $error)
        {
            return new JsonResponse(["errors" => $error->getMessage()], 401);  

        }

        return $updated_listing;
      
    }

    /**
     * Delete Listing
     * 
     * @param array $request
     * @return object 
     * 
     */

     //ensure images delete from cloudinary on delete
    public function destroy($id)
    {
       $listing = new Listing();
       try
       {
        return $listing->deleteListing($id);

       }catch (NotFoundException $error)
       {
           return new JsonResponse(["errors" => $error->getMessage()], 404);

       }catch (UnauthorizedUserException $error)
       {
           return new JsonResponse(["errors" => $error->getMessage()], 401);
       }
    }


}