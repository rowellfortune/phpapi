<?php 

require_once('db.php');
require_once('../model/response.php');

try{
    $writeDB = DB::connectWriteDB();
}
catch(PDOException $ex){
    error_log("Connection error: ".$ex, 0);
    $response = new Response();
    $response->setHttpStatusCode(500);
    $response->setSuccess(false);
    $response->addMessage("Database connection errorft");
    $response->send();
    exit;
}

//sessions = DELETE & PATCH - 
if (array_key_exists("sessionid", $_GET)) {  

    $sessionid = $_GET['sessionid'];

    if ($sessionid === '' || !is_numeric($sessionid)) {
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        ($sessionid === ' ' ? $response->addMessage('Session ID cannot be blank') : false);
        (!is_numeric($sessionid) === ' ' ? $response->addMessage('Sessioin ID must be nummeric') : false);
        $response->send();
        exit;
    }

    if (!isset($_SERVER['HTTP_AUTHORIZATION']) || strlen($_SERVER['HTTP_AUTHORIZATION']) < 1 ) {
        $response = new Response();
        $response->setHttpStatusCode(401);
        $response->setSuccess(false);
        (!isset($_SERVER['HTTP_AUTHORIZATION']) ? $response->addMessage('Access token is missing from the header') : false);
        (strlen($_SERVER['HTTP_AUTHORIZATION'] < 1) ? $response->addMessage('Access token cannot be blank') : false);
        $response->send();
        exit;
    }

    $accesstoken = $_SERVER['HTTP_AUTHORIZATION'];

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        header('Access-Control-Allow-Mehtods: DELETE, PATCH, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        header('Access-Control-Max-Age: 86400');
        $response = new Response();
        $response->setHttpStatusCode(200);
        $response->setSuccess(true);
        $response->send();
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        try {
            $query = $writeDB->prepare('DELETE FROM sessions WHERE id = :sessionid AND accesstoken = :accesstoken');
            $query->bindParam(':sessionid', $sessionid, PDO::PARAM_INT);
            $query->bindParam(':accesstoken', $accesstoken, PDO::PARAM_STR);
            $query->execute();


            $rowCount = $query->rowCount();

            if ($rowCount === 0) {
                $response = new Response();
                $response->setHttpStatusCode(500);
                $response->setSuccess(false);
                $response->addMessage("Failed to log out of this session using access token provided");
                $response->send();
                exit;
            }

            $returnData = array();
            $returnData['session_id'] = intval($sessionid);

            $response = new Response();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->addMessage("Logged out");
            $response->send();
            exit;

        } 
        catch (PDOException $ex) {
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("There was an issue logging out -  please try again");
            $response->send();
            exit;
        }  
    }
    elseif ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
        
        if ($_SERVER['CONTENT_TYPE'] !== 'application/json') {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage("Contnet Type header not set to JSON");
            $response->send();
            exit;
        }

        $rawPatchData = file_get_contents('php://input');

        if (!$jsonData = json_decode($rawPatchData)) {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage("Rquest body is not valid JSON");
            $response->send();
            exit;
        }

        if (!isset($jsonData->refresh_token) || strlen($jsonData->refresh_token) < 1) {
            $response = new Response();
            $response->setHttpStatusCode(401);
            $response->setSuccess(false);
            (!isset($jsonData->refresh_token) ? $response->addMessage('Refresh token not supplied') : false);
            (strlen($jsonData->refresh_token) < 1 ? $response->addMessage('Refresh token cannot be blank') : false);
            $response->send();
            exit;
        }

        try {
            
            $refreshtoken = $jsonData->refresh_token;

            $query = $writeDB->prepare('SELECT sessions.id AS sessionid, sessions.userid AS userid, accesstoken, refreshtoken, active, accesstokenexpiry, refreshtokenexpiry FROM sessions, user WHERE user.user_id = sessions.userid AND sessions.id = :sessionid AND sessions.accesstoken = :accesstoken AND sessions.refreshtoken = :refreshtoken');
            $query->bindParam(':sessionid', $sessionid, PDO::PARAM_INT);
            $query->bindParam(':accesstoken', $accesstoken, PDO::PARAM_STR);
            $query->bindParam(':refreshtoken', $refreshtoken, PDO::PARAM_STR);
            $query->execute();
            
            // get row count
            $rowCount = $query->rowCount();
    
            if($rowCount === 0) {
                // set up response for unsuccessful access token refresh attempt
                $response = new Response();
                $response->setHttpStatusCode(401);
                $response->setSuccess(false);
                $response->addMessage("Access Token or Refresh Token is incorrect for session id");
                $response->send();
                exit;
            }

            $row = $query->fetch(PDO::FETCH_ASSOC);
            
            // save returned details into variables
            $returned_sessionid = $row['sessionid'];
            $returned_userid = $row['userid'];
            $returned_accesstoken = $row['accesstoken'];
            $returned_refreshtoken = $row['refreshtoken'];
            $returned_useractive         = $row['active'];
            $returned_accesstokenexpiry = $row['accesstokenexpiry'];
            $returned_refreshtokenexpiry = $row['refreshtokenexpiry'];

            if ($returned_useractive !== 1) {
                $response = new Response();
                $response->setHttpStatusCode(401);
                $response->setSuccess(false);
                $response->addMessage("User account is not active");
                $response->send();
                exit;
            }

            if (strtotime($returned_refreshtokenexpiry) < time()) {
                $response = new Response();
                $response->setHttpStatusCode(401);
                $response->setSuccess(false);
                $response->addMessage("Refresh token has expired - please log in again");
                $response->send();
                exit;
            }

             // generate access token
            // use 24 random bytes to generate a token then encode this as base64
            // suffix with unix time stamp to guarantee uniqueness (stale tokens)
            $accesstoken = base64_encode(bin2hex(openssl_random_pseudo_bytes(24)).time());
    
            // generate refresh token
            // use 24 random bytes to generate a refresh token then encode this as base64
            // suffix with unix time stamp to guarantee uniqueness (stale tokens)
            $refreshtoken = base64_encode(bin2hex(openssl_random_pseudo_bytes(24)).time());
    
            // set access token and refresh token expiry in seconds (access token 20 minute lifetime and refresh token 14 days lifetime)
            // send seconds rather than date/time as this is not affected by timezones
            $access_token_expiry_seconds = 1200;
            $refresh_token_expiry_seconds = 1209600;

            // create the query string to update the current session row in the sessions table and set the token and refresh token as well as their expiry dates and times
            $query = $writeDB->prepare('update sessions set accesstoken = :accesstoken, accesstokenexpiry = date_add(NOW(), INTERVAL :accesstokenexpiryseconds SECOND), refreshtoken = :refreshtoken, refreshtokenexpiry = date_add(NOW(), INTERVAL :refreshtokenexpiryseconds SECOND) where id = :sessionid and userid = :userid and accesstoken = :returnedaccesstoken and refreshtoken = :returnedrefreshtoken');
            // bind the user id
            $query->bindParam(':userid', $returned_userid, PDO::PARAM_INT);
            // bind the session id
            $query->bindParam(':sessionid', $returned_sessionid, PDO::PARAM_INT);
            // bind the access token
            $query->bindParam(':accesstoken', $accesstoken, PDO::PARAM_STR);
            // bind the access token expiry date
            $query->bindParam(':accesstokenexpiryseconds', $access_token_expiry_seconds, PDO::PARAM_INT);
            // bind the refresh token
            $query->bindParam(':refreshtoken', $refreshtoken, PDO::PARAM_STR);
            // bind the refresh token expiry date
            $query->bindParam(':refreshtokenexpiryseconds', $refresh_token_expiry_seconds, PDO::PARAM_INT);
            // bind the old access token for where clause as user could have multiple sessions
            $query->bindParam(':returnedaccesstoken', $returned_accesstoken, PDO::PARAM_STR);
            // bind the old refresh token for where clause as user could have multiple sessions
            $query->bindParam(':returnedrefreshtoken', $returned_refreshtoken, PDO::PARAM_STR);
            // run the query
            $query->execute();

            
            // get count of rows updated - should be 1
            $rowCount = $query->rowCount();
            
            // check that a row has been updated
            if($rowCount === 0) {
                $response = new Response();
                $response->setHttpStatusCode(401);
                $response->setSuccess(false);
                $response->addMessage("Access token could not be refreshed - please log in again");
                $response->send();
                exit;
            }
    
            // build response data array which contains the session id, access token and refresh token
            $returnData = array();
            $returnData['session_id'] = $returned_sessionid;
            $returnData['access_token'] = $accesstoken;
            $returnData['access_token_expiry'] = $access_token_expiry_seconds;
            $returnData['refresh_token'] = $refreshtoken;
            $returnData['refresh_token_expiry'] = $refresh_token_expiry_seconds;
    
            $response = new Response();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->setData($returnData);
            $response->send();
            exit;
        } 
        catch (PDOException $ex) {
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("There was an issue refreshing access token - please log in again");
            $response->send();
            exit;
        }

    } 
    else {
        $response = new Response();
        $response->setHttpStatusCode(405);
        $response->setSuccess(false);
        $response->addMessage("Request method not allowed");
        $response->send();
        exit;
    }
}
//sessions = POST - create a session/log in
elseif (empty($_GET)) {

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        header('Access-Control-Allow-Mehtods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        header('Access-Control-Max-Age: 86400');
        $response = new Response();
        $response->setHttpStatusCode(200);
        $response->setSuccess(true);
        $response->send();
        exit;
    }
    
    if($_SERVER['REQUEST_METHOD'] !== 'POST'){
        $response = new Response();
        $response->setHttpStatusCode(405);
        $response->setSuccess(false);
        $response->addMessage("Request method not allowed");
        $response->send();
        exit;
    }

    sleep(1);

    if ($_SERVER['CONTENT_TYPE'] !== 'application/json') {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("Content Type header not set to JSON");
        $response->send();
        exit;
    }
    
    $rawPostData = file_get_contents('php://input');

    if (!$jsonData = json_decode($rawPostData)) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("Request body is not valid JSON");
        $response->send();
        exit;
    }

    if (!isset($jsonData->name) || !isset($jsonData->password)) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        (!isset($jsonData->name) ? $response->addMessage("Name not supplied") : false );
        (!isset($jsonData->password) ? $response->addMessage("Password not supplied") : false );
        $response->send();
        exit;
    }

    if(strlen($jsonData->name) < 1  || strlen($jsonData->name) > 255  || strlen($jsonData->password) < 1 || strlen($jsonData->password) > 255){
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        (strlen($jsonData->name) < 1 ? $response->addMessage("Username can not be blank") : false );
        (strlen($jsonData->name) > 255 ? $response->addMessage("Username must be less than 255 characters") : false );
        (strlen($jsonData->password) < 1 ? $response->addMessage("Password can not be blank") : false );
        (strlen($jsonData->password) > 255 ? $response->addMessage("Password must be let than 255 characters") : false );
        $response->send();
        exit;
    }

    try {
        $name = $jsonData->name;
        $password = $jsonData->password;

        $query = $writeDB->prepare('SELECT user_id, name, password, active FROM user WHERE name = :name');
        $query->bindParam(':name', $name, PDO::PARAM_STR);
        $query->execute();

        $rowCount = $query->rowCount();

        if($rowCount === 0){
            $response = new Response();
            $response->setHttpStatusCode(401);
            $response->setSuccess(false);
            $response->addMessage("Username of Password is incorrect");
            $response->send();
            exit;
        }

        $row = $query->fetch(PDO::FETCH_ASSOC);

        $returned_id = $row['user_id'];
        $returned_username = $row['name'];
        $returned_password = $row['password'];
        $returned_useractive = $row['active'];

        if ($returned_useractive !== 1) {
            $response = new Response();
            $response->setHttpStatusCode(401);
            $response->setSuccess(false);
            $response->addMessage("User account not active");
            $response->send();
            exit;
        }

        if(!password_verify($password, $returned_password)){
            // $query = $writeDB->prepare('UPDATE user SET loginattempts = loginattempts+1 WHERE user_id = :id');
            // $query->bindparam(':id', $returned_id, PDO::PARAM_INT);
            // $query->execute();

            $response = new Response();
            $response->setHttpStatusCode(401);
            $response->setSuccess(false);
            $response->addMessage("Username of password is incorrect");
            $response->send();
            exit;
        }
        
        $accesstoken = base64_encode(bin2hex(openssl_random_pseudo_bytes(24)).time());
        $refreshtoken = base64_encode(bin2hex(openssl_random_pseudo_bytes(24)).time());

        $access_token_expiry_seconds = 600;
        $refresh_token_expiry_seconds = 1209600;

    } catch (PDOException $ex) {
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("There was an issue logging in");
        $response->send();
        exit;
    }
    
    try {
        $writeDB->beginTransaction();

        $query = $writeDB->prepare('INSERT INTO sessions (userid, accesstoken, accesstokenexpiry, refreshtoken, refreshtokenexpiry) VALUES (:userid, :accesstoken, date_add(NOW(), INTERVAL :accesskotenexpiryseconds SECOND), :refreshtoken, date_add(NOW(), INTERVAL :refreshtokenexpriryseconds SECOND))');
        $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
        $query->bindParam(':accesstoken', $accesstoken, PDO::PARAM_STR);
        $query->bindParam(':accesskotenexpiryseconds', $access_token_expiry_seconds, PDO::PARAM_INT);
        $query->bindParam(':refreshtoken', $refreshtoken, PDO::PARAM_STR);
        $query->bindParam(':refreshtokenexpriryseconds', $refresh_token_expiry_seconds, PDO::PARAM_INT);
        $query->execute();

        $lastSessionID = $writeDB->lastInsertId();

        $writeDB->commit();

        $returnData = array();
        $returnData['session_id'] = intval($lastSessionID);
        $returnData['access_token'] = $accesstoken;
        $returnData['access_token_expires_in'] = $access_token_expiry_seconds;
        $returnData['refresh_token'] = $refreshtoken;
        $returnData['refresh_token_expires_in'] = $refresh_token_expiry_seconds;

        $response = new Response();
        $response->setHttpStatusCode(201);
        $response->setSuccess(true);
        $response->addMessage("Token Create - Logged in");
        $response->setData($returnData);
        $response->send();
        exit;
    } 
    catch (PDOException $ex) {
        $writeDB->rollBack();
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("There was an issue logging in - please try again");
        $response->send();
        exit;
    }

}
else {
    $response = new Response();
    $response->setHttpStatusCode(404);
    $response->setSuccess(false);
    $response->addMessage("Endpoint not found");
    $response->send();
    exit;
}