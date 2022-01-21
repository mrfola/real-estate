# Real Estate

This is an API created using PHP that allows users to list apartments, set prices, description, etc and get other people to pay for the selected apartments. 

PLEASE NOTE: This API is still under development and there are probably many bugs at the moment. Updates would be coming up over the next few months. 

If you want to access the API online, you view the postman documentation [here](https://documenter.getpostman.com/view/6834602/UVREjPhp)

To access this API locally, keep reading.

## Requirements

To access this project locally you are required to have the following: 

- A web server (XAMPP or any other alternative)
- Composer installed

## Installation

### 1. Install Dependencies

After cloning the repo to your local machine, use [composer](https://getcomposer.org/) to install dependencies.

```bash
composer install
```

### 2. Rename .env.example to env

### 3. Environment Configuration

#### a. App Environment
```php
# can either be "local" or "testing". 
# testing should only be used when you want to run unit tests for your application. 
# Never run unit tests on local enviroment - you risk losing all your database information

APP_ENV=local


```
#### b. Database Configuration

```php

# setup your local database credentials
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=database
DB_USERNAME=username
DB_PASSWORD=password

# your test database credentials
TEST_DB_CONNECTION=mysql
TEST_DB_HOST=127.0.0.1
TEST_DB_PORT=3306
TEST_DB_DATABASE=test-database
TEST_DB_USERNAME=username
TEST_DB_PASSWORD=password
TEST_BASE_URL=localhost:8000

```
#### c. Cloudinary Configuration

The API uses cloudinary to host listing apartment images and you are required to setup your cloudinary account before you can create listings. You can still create an account before doing this step.

Your Cloudinary credentials can be found on the Dashboard page of your [Cloudinary account](https://cloudinary.com/users/login).

```php
# setup your cloudinary account

CLOUDINARY_URL=
CLOUDINARY_CLOUD_NAME=
CLOUDINARY_API_KEY=
CLOUDINARY_API_SECRET=

```

#### d. Flutterwave Configuration

The API uses Flutterwave to process payments for apartment listings. You are required to configure your Flutterwave account before making payments. 

To get your Flutterwave credentials, kindly check this [article by flutterwave](https://support.flutterwave.com/en/articles/3632726-my-api-keys)

PLEASE NOTE: KINDLY USE TEST CREDENTIALS IN YOUR ENV. DON'T USE LIVE CREDENTIALS ELSE YOU WILL BE DEBITTED.

```php
# setup your cloudinary account

FLUTTERWAVE_SECRET_KEY=
FLUTTERWAVE_ENCRYPTION_KEY=

```

## Usage

To test the API from Postman (or any other similar tool), you need to startup your PHP server first. Go into your project directory and run the following command:

```php

php -S 127.0.0.1:8000

```
This will serve your API from 127.0.0.1:8000. 

You can then access the endpoints from Postman. 

### Access Endpoints
To see what endpoints are available and the values they take, check the postman documentation [here](https://documenter.getpostman.com/view/6834602/UVREjPhp)

PLEASE NOTE: When accessing the endpoints from your local machine, replace "https://real-estate-v1.herokuapp.com" in your endpoints with "localhost:8000"

"https://real-estate-v1.herokuapp.com" is the base url for the live API, but your local base url will be "localhost:8000"

## Testing the API
If you would like run unit tests on the application, simply run the following command:
```php

composer test

```
You can also add new unit test by creating a test class (E.g AuthorizationTest.php) in the test directory.

Be sure to include the correct namespace in your document to follow PSR-4 standards. 

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

## Questions and Collaborations
If you have any questions regarding this project (or any other project) you can send me an email at [folaranmijesutofunmi[at]gmail.com](mailto:folaranmijesutofunmi@gmail.com) and I'll respond as soon as I can. 

## Other projects you might love
* [Book Repository](https://github.com/mrfola/book-repository)
* [Job Scraper](https://github.com/mrfola/laravelJobScraper)
