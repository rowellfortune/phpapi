<?php

use GuzzleHttp\Psr7\Message;

require_once('db.php');
require_once('../model/response.php');
require_once('../model/im_msg.php');


try
{
    $writeDB = DB::connectWriteDB();
    $readDB  = DB::connectReadDB();
}

catch (PDOException $ex) {
    error_log("Connection error - ".$ex, 0);
    $response = new Response();
    $response->setHttpStatusCode(500);
    $response->setSuccess(false);
    $response->addMessage("Database connection error");
    $response->send();
    exit;
}

if(array_key_exists("id", $_GET)){
    $id = $_GET['id'];

    if($id == '' || !is_numeric($id)){
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("User ID cannot or must be numeric");
        $response->send();
        exit;
    }
    if($_SERVER['REQUEST_METHOD'] === 'GET'){
        try {
            $query = $readDB->prepare('SELECT * FROM im_msg WHERE id = :id');
            $query->bindParam(':id', $id, PDO::PARAM_INT);
            $query->execute();

            $rowCount = $query->rowCount();

            if($rowCount === 0){
                $response = new Response();
                $response->setHttpStatusCode(404);
                $response->setSuccess(false);
                $response->addMessage("Task not found");
                $response->send();
                exit;
            }

            while($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $message = new ImMsg(
                    $row['id'],
                    $row['from_user'],
                    $row['from_group_id'],
                    $row['to_user'],
                    $row['to_group_id'],
                    $row['group_id'],
                    $row['born'],
                    $row['name'],
                    $row['msg'],
                    $row['ip'],
                    $row['is_new'],
                    $row['system'],
                    $row['system_type'],
                    $row['from_user_deleted'],
                    $row['to_user_deleted'],
                    $row['msg_translation'],
                    $row['send'],
                    $row['audio_message_id'],
                    $row['msg_hash']
                );

                $imMsgArray[] = $message->returnImMsgAsArray();
            }

            $returnData = array();
            $returnData['rows_returned'] = $rowCount;
            $returnData['messages'] = $imMsgArray;

            $response = new Response();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->toCache(true);
            $response->setData($returnData);
            $response->send();
            exit;
        }
        # Error from server
        catch (UserException $ex){
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage($ex->getMessage());
            $response->send();
            exit;
        }
        # Faild to get data from the database
        catch (PDOException $ex){
            error_log("Database query error - ".$ex, 0);
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("Faild to get user");
            $response->send();
            exit;
        }
    }

    elseif($_SERVER['REQUEST_METHOD'] === 'DELETE') {

        try {
            $query = $writeDB->prepare('DELETE from im_msg where id = :id');
            $query->bindParam(':im_msg', $im_msg, PDO::PARAM_INT);
            $query->execute();
            $rowCount = $query->rowCount();

            if($rowCount == 0){
                $response = new Response();
                $response->setHttpStatusCode(404);
                $response->setSuccess(false);
                $response->addMessage("Task no found");
                $response->send();
                exit;
            }

            $response = new Response();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->toCache(true);
            $response->addMessage("Message deleted");
            $response->send();
            exit;
        }
        catch (PDOException $ex) {
            error_log("Database query error - ".$ex, 0);
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("Faild to delete message");
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
        
        try {
            
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
elseif(array_key_exists("page", $_GET) && array_key_exists("chat", $_GET) && array_key_exists("to_user", $_GET) && array_key_exists("from_user", $_GET)) {    
    
    $page = $_GET['page'];
    $chat =  $_GET['chat'];
    $to_user =  $_GET['to_user'];
    $from_user =  $_GET['from_user'];
   
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        $response = new Response();
        $response->setHttpStatusCode(405);
        $response->setSuccess(false);
        $response->addMessage("Request method not allowed");
        $response->send();
        exit;
    } 
 
    if($chat == '' || !is_numeric($page)){
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("page cannot empty or must be numeric");
        $response->send();
        exit();
    }

     if($chat == '' || !is_numeric($chat)){
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("new cannot empty or must be numeric");
        $response->send();
        exit();
    }

    if($to_user == '' || !is_numeric($to_user)){
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("new cannot empty or must be numeric");
        $response->send();
        exit();
    }

    if($from_user == '' || !is_numeric($from_user)){
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("new cannot empty or must be numeric");
        $response->send();
        exit();
    }

    $limitPerPage = 5; 
    
    if($_SERVER['REQUEST_METHOD'] === 'GET'){

        try {
           
            $query = $readDB->prepare('select count(id) as totalMessages from im_msg');
            $query->execute();

            $row = $query->fetch(PDO::FETCH_ASSOC);

            $messageCount = intval($row['totalMessages']);

            $numOfPages = ceil($messageCount/$limitPerPage);

            if($numOfPages == 0 ){
                $numOfPages = 1;
            }

            if($page > $numOfPages || $page == 0){
                $response = new Response();
                $response->setHttpStatusCode(404);
                $response->setSuccess(false);
                $response->addMessage("Page not found");
                $response->send();
                exit();
            }

            $offset = ($page == 1 ? 0 : ($limitPerPage*($page-1)));

            $query = $readDB->prepare('SELECT * FROM im_msg WHERE is_new = :chat AND to_user = :to_user AND from_user = :from_user AND system = 0 limit :pglimit offset :offset');
            $query->bindParam(':pglimit', $limitPerPage, PDO::PARAM_INT);
            $query->bindParam(':offset', $offset, PDO::PARAM_INT);
            $query->bindParam(':chat', $chat, PDO::PARAM_INT);
            $query->bindParam(':to_user', $to_user, PDO::PARAM_INT);
            $query->bindParam(':from_user', $from_user, PDO::PARAM_INT);
            $query->execute();
            
            $rowCount = $query->rowCount();

            $imMsgArray = array();

            if($rowCount === 0){
                $response = new Response();
                $response->setHttpStatusCode(404);
                $response->setSuccess(false);
                $response->addMessage("Message not found");
                $response->send();
                exit;
            }

            while($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $message = new ImMsg(
                    $row['id'],
                    $row['from_user'],
                    $row['from_group_id'],
                    $row['to_user'],
                    $row['to_group_id'],
                    $row['group_id'],
                    $row['born'],
                    $row['name'],
                    $row['msg'],
                    $row['ip'],
                    $row['is_new'],
                    $row['system'],
                    $row['system_type'],
                    $row['from_user_deleted'],
                    $row['to_user_deleted'],
                    $row['msg_translation'],
                    $row['send'],
                    $row['audio_message_id'],
                    $row['msg_hash']
                );
                $imMsgArray[] = $message->returnImMsgAsArray();
            }

            $returnData = array();
            $returnData['rows_returned'] = $rowCount;
            $returnData['messages'] = $imMsgArray;

            $response = new Response();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->toCache(true);
            $response->setData($returnData);
            $response->send();
            exit;
            
        }catch (UserException $ex){
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage($ex->getMessage());
            $response->send();
            exit();
        }catch (PDOException $ex){
            error_log("Database query error - ".$ex, 0);
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("Faild to get user");
            $response->send();
            exit();
        }

    } else {
        $response = new Response();
        $response->setHttpStatusCode(405);
        $response->setSuccess(false);
        $response->addMessage("Request method not allowed");
        $response->send();
        exit();
    }
} 
elseif (array_key_exists("new", $_GET)) {
    $new =  $_GET['new'];
   
    if($new == '' || !is_numeric($new)){
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("new cannot or must be numeric");
        $response->send();
        exit();
    }

    if($_SERVER['REQUEST_METHOD'] === 'GET'){
        try {
            $query = $readDB->prepare('SELECT * FROM im_msg WHERE is_new = :new AND system = 0');
            $query->bindParam(':new', $new, PDO::PARAM_INT);
            $query->execute();
            
            $rowCount = $query->rowCount();

            // $imMsgArray = array();

            if($rowCount === 0){
                $response = new Response();
                $response->setHttpStatusCode(404);
                $response->setSuccess(false);
                $response->addMessage("Task not found");
                $response->send();
                exit;
            }

            while($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $message = new ImMsg(
                    $row['id'],
                    $row['from_user'],
                    $row['from_group_id'],
                    $row['to_user'],
                    $row['to_group_id'],
                    $row['group_id'],
                    $row['born'],
                    $row['name'],
                    $row['msg'],
                    $row['ip'],
                    $row['is_new'],
                    $row['system'],
                    $row['system_type'],
                    $row['from_user_deleted'],
                    $row['to_user_deleted'],
                    $row['msg_translation'],
                    $row['send'],
                    $row['audio_message_id'],
                    $row['msg_hash']
                );
                $imMsgArray[] = $message->returnImMsgAsArray();
            }

            $returnData = array();
            $returnData['rows_returned'] = $rowCount;
            $returnData['messages'] = $imMsgArray;

            $response = new Response();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->toCache(true);
            $response->setData($returnData);
            $response->send();
            exit;
            
        }catch (UserException $ex){
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage($ex->getMessage());
            $response->send();
            exit();
        }catch (PDOException $ex){
            error_log("Database query error - ".$ex, 0);
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("Faild to get user");
            $response->send();
            exit();
        }

    } 
    else {
        $response = new Response();
        $response->setHttpStatusCode(405);
        $response->setSuccess(false);
        $response->addMessage("Request method not allowed");
        $response->send();
        exit();
    }
} 
elseif (array_key_exists("user_id", $_GET)) {
    $user_id =  $_GET['user_id'];

    if($user_id == '' || !is_numeric($user_id)){
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("User ID cannot or must be numeric");
        $response->send();
        exit;
    }

    if($_SERVER['REQUEST_METHOD'] === 'GET'){
        try {
            $query = $readDB->prepare('SELECT M.*, U.name as user_from,
            U.use_as_online AS u_from_fake, U.register AS u_from_register,
            U2.name as user_to,
            U2.use_as_online AS u_to_fake, U2.register AS u_to_register
            FROM im_msg as M
            JOIN user as U on U.user_id = M.from_user
            JOIN user as U2 on U2.user_id = M.to_user WHERE U.user_id = :user_id');
            $query->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $query->execute();
            
            $rowCount = $query->rowCount();

            $imMsgArray = array();

            while($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $message = new ImMsg(
                    $row['id'],
                    $row['from_user'],
                    $row['from_group_id'],
                    $row['to_user'],
                    $row['to_group_id'],
                    $row['group_id'],
                    $row['born'],
                    $row['name'],
                    $row['msg'],
                    $row['ip'],
                    $row['is_new'],
                    $row['system'],
                    $row['system_type'],
                    $row['from_user_deleted'],
                    $row['to_user_deleted'],
                    $row['msg_translation'],
                    $row['send'],
                    $row['audio_message_id'],
                    $row['msg_hash']
                );
                $imMsgArray[] = $message->returnImMsgAsArray();
            }

            $returnData = array();
            $returnData['rows_returned'] = $rowCount;
            $returnData['messages'] = $imMsgArray;

            $response = new Response();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->toCache(true);
            $response->setData($returnData);
            $response->send();
            exit;
            
        }catch (UserException $ex){
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage($ex->getMessage());
            $response->send();
            exit();
        }catch (PDOException $ex){
            error_log("Database query error - ".$ex, 0);
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("Faild to get message");
            $response->send();
            exit;
        }

    } else {
        $response = new Response();
        $response->setHttpStatusCode(405);
        $response->setSuccess(false);
        $response->addMessage("Request method not allowed");
        $response->send();
        exit;
    }

} 
elseif (array_key_exists("page", $_GET)){
   
    if($_SERVER['REQUEST_METHOD'] === 'GET'){

        $page = $_GET['page'];

        if($page == '' || !is_numeric($page)){
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage("Page nummer cannot be blank and must be numeric");
            $response->send();
            exit;
        }

        $limitPerPage = 50; 

        try {

            $query = $readDB->prepare('select count(id) as totalMessages from im_msg');
            $query->execute();

            $row = $query->fetch(PDO::FETCH_ASSOC);

            $messageCount = intval($row['totalMessages']);

            $numOfPages = ceil($messageCount/$limitPerPage);

            if($numOfPages == 0 ){
                $numOfPages = 1;
            }

            if($page > $numOfPages || $page == 0){
                $response = new Response();
                $response->setHttpStatusCode(404);
                $response->setSuccess(false);
                $response->addMessage("Page not found");
                $response->send();
                exit();
            }

            $offset = ($page == 1 ? 0 : ($limitPerPage*($page-1)));

            $query = $readDB->prepare('SELECT M.*, U.name as user_from,
            U.use_as_online AS u_from_fake, U.register AS u_from_register,
            U2.name as user_to,
            U2.use_as_online AS u_to_fake, U2.register AS u_to_register
            FROM im_msg as M
            JOIN user as U on U.user_id = M.from_user
            JOIN user as U2 on U2.user_id = M.to_user limit :pglimit offset :offset');
            $query->bindParam(':pglimit', $limitPerPage, PDO::PARAM_INT);
            $query->bindParam(':offset', $offset, PDO::PARAM_INT);
            $query->execute();

            $rowCount = $query->rowCount();

            $imMsgArray = array();

            while($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $message = new ImMsg(
                    $row['id'],
                    $row['from_user'],
                    $row['from_group_id'],
                    $row['to_user'],
                    $row['to_group_id'],
                    $row['group_id'],
                    $row['born'],
                    $row['name'],
                    $row['msg'],
                    $row['ip'],
                    $row['is_new'],
                    $row['system'],
                    $row['system_type'],
                    $row['from_user_deleted'],
                    $row['to_user_deleted'],
                    $row['msg_translation'],
                    $row['send'],
                    $row['audio_message_id'],
                    $row['msg_hash']
                );
                $imMsgArray[] = $message->returnImMsgAsArray();
            }

            $returnData = array();
            $returnData['rows_returned'] = $rowCount;
            $returnData['total_rows'] = $messageCount;
            $returnData['total_pages'] = $numOfPages;
            ($page < $numOfPages ? $returnData['has_next_page'] = true : $returnData['has_next_page']  = false);
            ($page > 1 ? $returnData['has_previous_page'] = true : $returnData['has_previous_page']  = false);
            $returnData['messages'] = $imMsgArray;
            
            $response = new Response();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->toCache(true);
            $response->setData($returnData);
            $response->send();
            exit;
            
        }  
        # Error from server
        catch (UserException $ex){
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage($ex->getMessage());
            $response->send();
            exit();
        }
        # Faild to get data from the database
        catch (PDOException $ex){
            error_log("Database query error - ".$ex, 0);
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("Faild to get message - Database error");
            $response->send();
            exit;
        }

    } else {
        $response = new Response();
        $response->setHttpStatusCode(405);
        $response->setSuccess(false);
        $response->addMessage("Request method not allowed");
        $response->send();
        exit;
    }
    
} 
elseif (empty($_GET)){ 
     
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        header('Access-Control-Allow-Mehtods: POST, GET, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        header('Access-Control-Max-Age: 86400');
        $response = new Response();
        $response->setHttpStatusCode(200);
        $response->setSuccess(true);
        $response->send();
        exit;
    }

    if($_SERVER['REQUEST_METHOD'] === 'GET') {

        try {
            $query = $readDB->prepare('SELECT M.*, U.name as user_from,
            U.use_as_online AS u_from_fake, U.register AS u_from_register,
            U2.name as user_to,
            U2.use_as_online AS u_to_fake, U2.register AS u_to_register
            FROM im_msg as M
            JOIN user as U on U.user_id = M.from_user
            JOIN user as U2 on U2.user_id = M.to_user');
            $query->execute();

            $rowCount = $query->rowCount();
            
            $imMsgArray = array();

            while($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $message = new ImMsg(
                    $row['id'],
                    $row['from_user'],
                    $row['from_group_id'],
                    $row['to_user'],
                    $row['to_group_id'],
                    $row['group_id'],
                    $row['born'],
                    $row['name'],
                    $row['msg'],
                    $row['ip'],
                    $row['is_new'],
                    $row['system'],
                    $row['system_type'],
                    $row['from_user_deleted'],
                    $row['to_user_deleted'],
                    $row['msg_translation'],
                    $row['send'],
                    $row['audio_message_id'],
                    $row['msg_hash']
                );
                $imMsgArray[] = $message->returnImMsgAsArray();
            }

            $returnData = array();
            $returnData['rows_returned'] = $rowCount;
            $returnData['messages'] = $imMsgArray;

            $response = new Response();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->toCache(true);
            $response->setData($returnData);
            $response->send();
            exit;
        }
        # Error from server
        catch (UserException $ex){
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage($ex->getMessage());
            $response->send();
            exit();
        }
        # Faild to get data from the database
        catch (PDOException $ex){
            error_log("Database query error - ".$ex, 0);
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("Faild to get message - Database error");
            $response->send();
            exit;
        }
    }
    elseif($_SERVER['REQUEST_METHOD'] === 'POST') {

        // create task
       try {
            // check request's content type header is JSON
            if ($_SERVER['CONTENT_TYPE'] !== 'application/json') {
                // set up response for unsuccessful request
                $response = new Response();
                $response->setHttpStatusCode(400);
                $response->setSuccess(false);
                $response->addMessage("Content type header is not set to JSON");
                $response->send();
                exit;
            }

        // get POST request body as the POSTed data will be JSON format
        $rawPostData = file_get_contents('php://input');

        if(!$jsonData = json_decode($rawPostData)) {
            // set up response for unsuccessful request
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage("Request body is not valid JSON");
            $response->send();
            exit;    
        }

        if (
            !isset($jsonData->from_user)            || 
            !isset($jsonData->from_group_id)        || 
            !isset($jsonData->to_user)              || 
            !isset($jsonData->to_group_id)          ||
            !isset($jsonData->group_id)             ||
            !isset($jsonData->name)                 ||
            !isset($jsonData->msg)                  ||
            !isset($jsonData->ip)                   ||
            !isset($jsonData->is_new)               ||
            !isset($jsonData->born)                 ||
            !isset($jsonData->system)               ||
            !isset($jsonData->system_type)          ||
            !isset($jsonData->from_user_deleted)    ||
            !isset($jsonData->to_user_deleted)      ||
            !isset($jsonData->msg_translation)      ||
            !isset($jsonData->send)                 ||
            !isset($jsonData->audio_message_id)     ||  
            !isset($jsonData->msg_hash)          
        ) {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            ( !isset($jsonData->from_user)        ? $response->addMessage("From user field is mandatory and must be provided") : false);
            (!isset($jsonData->from_group_id)     ? $response->addMessage("From group ID field is mandatory and must be provided") : false);
            (!isset($jsonData->to_user)           ? $response->addMessage("To user field is mandatory and must be provided") : false);
            (!isset($jsonData->to_group_id)       ? $response->addMessage("To group ID field is mandatory and must be provided"): false);
            (!isset($jsonData->group_id)          ? $response->addMessage("Group ID field is mandatory and must be provided"): false);
            (!isset($jsonData->name)              ? $response->addMessage("Name field is mandatory and must be provided"): false);
            (!isset($jsonData->msg)               ? $response->addMessage("Message field is mandatory and must be provided"): false);
            (!isset($jsonData->ip)                ? $response->addMessage("IP field is mandatory and must be provided"): false);
            (!isset($jsonData->is_new)            ? $response->addMessage("Is new field is mandatory and must be provided"): false);
            (!isset($jsonData->born)              ? $response->addMessage("Born field is mandatory and must be provided"): false);
            (!isset($jsonData->system)            ? $response->addMessage("System field is mandatory and must be provided"): false);
            (!isset($jsonData->system_type)       ? $response->addMessage("System type field is mandatory and must be provided"): false);
            (!isset($jsonData->from_user_deleted) ? $response->addMessage("From user deleted field is mandatory and must be provided"): false);
            (!isset($jsonData->to_user_deleted)   ? $response->addMessage("To user deleted field is mandatory and must be provided"): false);
            (!isset($jsonData->msg_translation)   ? $response->addMessage("Message translation field is mandatory and must be provided"): false);
            (!isset($jsonData->send)              ? $response->addMessage("Send field is mandatory and must be provided"): false);
            (!isset($jsonData->audio_message_id)  ? $response->addMessage("Audio message id field is mandatory and must be provided") : false);
            (!isset($jsonData->msg_hash)          ? $response->addMessage("Message hash field is mandatory and must be provided") : false);
            $response->send();
            exit;
        }
        // create new task with data, if non mandatory fields not provided then set to null
        $newMessage = new ImMsg(
            null
            ,(isset($jsonData->from_user)           ? $jsonData->from_user          : null)        
            ,(isset($jsonData->from_group_id)       ? $jsonData->from_group_id      : null)    
            ,(isset($jsonData->to_user)             ? $jsonData->to_user            : null)         
            ,(isset($jsonData->to_group_id)         ? $jsonData->to_group_id        : null)     
            ,(isset($jsonData->group_id)            ? $jsonData->group_id           : null)        
            ,(isset($jsonData->born)                ? $jsonData->born               : null)  
            ,(isset($jsonData->name)                ? $jsonData->name               : null)            
            ,(isset($jsonData->msg)                 ? $jsonData->msg                : null)             
            ,(isset($jsonData->ip)                  ? $jsonData->ip                 : null)             
            ,(isset($jsonData->is_new)              ? $jsonData->is_new             : null)         
            ,(isset($jsonData->system)              ? $jsonData->system             : null)          
            ,(isset($jsonData->system_type)         ? $jsonData->system_type        : null)     
            ,(isset($jsonData->from_user_deleted)   ? $jsonData->from_user_deleted  : null)
            ,(isset($jsonData->to_user_deleted)     ? $jsonData->to_user_deleted    : null) 
            ,(isset($jsonData->msg_translation)     ? $jsonData->msg_translation    : null) 
            ,(isset($jsonData->send)                ? $jsonData->send               : null)           
            ,(isset($jsonData->audio_message_id)    ? $jsonData->audio_message_id   : null)
            ,(isset($jsonData->msg_hash)            ? $jsonData->msg_hash           : null)       
        );
        // get title, description, deadline, completed and store them in variables
        $from_user               = $newMessage->getFromUser();          
        $from_group_id           = $newMessage->getGroupId();            
        $to_user                 = $newMessage->getToUser();            
        $to_group_id             = $newMessage->getToGroupId();       
        $group_id                = $newMessage->getGroupId(); 
        $born                    = $newMessage->getBorn();                           
        $name                    = $newMessage->getName();                
        $msg                     = $newMessage->getMsg();                 
        $ip                      = $newMessage->getIp();                   
        $is_new                  = $newMessage->getIsNew();                
        $system                  = $newMessage->getSystem();             
        $system_type             = $newMessage->getSystemType();      
        $from_user_deleted       = $newMessage->getFromUserDeleted();  
        $to_user_deleted         = $newMessage->getToUserDeleted();    
        $msg_translation         = $newMessage->getMsgTranslation();   
        $send                    = $newMessage->getSend();            
        $audio_message_id        = $newMessage->getAudioMessageId(); 
        $msg_hash                = $newMessage->getMsgHash();

        // create db query
        $query = $writeDB->prepare('INSERT 
        INTO im_msg( from_user,  from_group_id,  to_user,  to_group_id,  group_id,  name,  msg,  ip,  is_new, born, system,  system_type,  from_user_deleted,  to_user_deleted,  msg_translation,  send,  audio_message_id,  msg_hash) 
              VALUE(:from_user, :from_group_id, :to_user, :to_group_id, :group_id, :name, :msg, :ip, :is_new, :born, :system, :system_type, :from_user_deleted, :to_user_deleted, :msg_translation, :send, :audio_message_id, :msg_hash)');
        $query->bindParam(':from_user', $from_user, PDO::PARAM_INT);
        $query->bindParam(':from_group_id', $from_group_id, PDO::PARAM_INT);
        $query->bindParam(':to_user', $to_user, PDO::PARAM_INT);
        $query->bindParam(':to_group_id', $to_group_id, PDO::PARAM_INT);
        $query->bindParam(':group_id', $group_id, PDO::PARAM_INT);
        $query->bindParam(':name', $name, PDO::PARAM_STR);
        $query->bindParam(':msg', $msg, PDO::PARAM_STR);
        $query->bindParam(':ip', $ip, PDO::PARAM_STR);
        $query->bindParam(':born', $born, PDO::PARAM_STR);
        $query->bindParam(':is_new', $is_new, PDO::PARAM_INT);
        $query->bindParam(':system',$system, PDO::PARAM_INT);
        $query->bindParam(':system_type', $system_type, PDO::PARAM_INT);
        $query->bindParam(':from_user_deleted', $from_user_deleted, PDO::PARAM_INT);
        $query->bindParam(':to_user_deleted', $to_user_deleted, PDO::PARAM_INT);
        $query->bindParam(':msg_translation', $msg_translation, PDO::PARAM_STR);
        $query->bindParam(':send', $send, PDO::PARAM_INT);
        $query->bindParam(':audio_message_id', $audio_message_id, PDO::PARAM_INT);
        $query->bindParam(':msg_hash', $msg_hash, PDO::PARAM_STR);
        $query->execute();

        // get row count
        $rowCount = $query->rowCount();

        // check if row was actually inserted, PDO exception should have caught it if not.
        if ($rowCount === 0) {
            // set up response for unsuccessful return
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage("Failed to create task");
            $response->send();
            exit;
        }

        // get last task id so we can return the Task in the json
        $lastMessageID = $writeDB->lastInsertId();
        // create db query to get newly created task - get from master db not read slave as replication may be too slow for successful read
        $query = $writeDB->prepare('SELECT id, from_user,  from_group_id,  to_user,  to_group_id,  group_id,  name,  msg,  ip,  is_new,  system,  system_type,  from_user_deleted,  to_user_deleted,  msg_translation,  send,  audio_message_id,  msg_hash FROM im_msg WHERE id = :im_msg_id');
        $query->bindParam(':im_msg_id', $lastMessageID, PDO::PARAM_INT);
        $query->execute();
          
        // get row count
        $rowCount = $query->rowCount();
        
        // make sure that the new task was returned
        if ($rowCount === 0) {
            // set up response for unsuccessful return
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("Fail to retrieve message after creation");
            $response->send();
            exit;
        }

        $rowCount = $query->rowCount();

        $imMsgArray = array();
        // create empty array to store tasks
        $imMsgArray = array();

        // for each row returned - should be just one
        while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $message = new ImMsg(
                $row['id'],
                $row['from_user'],
                $row['from_group_id'],
                $row['to_user'],
                $row['to_group_id'],
                $row['group_id'],
                $row['born'],
                $row['name'],
                $row['msg'],
                $row['ip'],
                $row['is_new'],
                $row['system'],
                $row['system_type'],
                $row['from_user_deleted'],
                $row['to_user_deleted'],
                $row['msg_translation'],
                $row['send'],
                $row['audio_message_id'],
                $row['msg_hash']
            );
            $imMsgArray[] = $message->returnImMsgAsArray();
        }

        $returnData = array();
        $returnData['rows_returned'] = $rowCount;
        $returnData['message'] = $imMsgArray;

        //set up response for successful return
        $response = new Response();
        $response->setHttpStatusCode(201);
        $response->setSuccess(true);
        $response->addMessage("Task created");
        $response->setData($returnData);
        $response->send();
        exit; 

       }
        catch(TaskException $ex) {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage($ex->getMessage());
            $response->send();
            exit;
        }
       catch (PDOException $ex) {
        error_log("Database query error - ".$ex, 0);
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("Failed to insert message into database - check submitted data for errors");
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
else {
    $response = new Response();
    $response->setHttpStatusCode(404);
    $response->setSuccess(false);
    $response->addMessage("Endpoint Not Found ");
    $response->send();
    exit;
}