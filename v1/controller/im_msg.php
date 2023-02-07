<?php

require_once('db.php');
require_once('../model/response.php');
require_once('../model/im_msg.php');

$allowed_chats = array("new", "to_user", "from_user");
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
    elseif($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $query = $writeDB->prepare('INSERT into im_msg msg = :msg');
            $query->bindParam(':im_msg', $im_msg, PDO::PARAM_INT);
            $query->execute();
            $rowCount = $query->rowCount();

        } catch (\Throwable $th) {
            //throw $th;
        }

    }
    elseif($_SERVER['REQUEST_METHOD'] === 'PATCH') {
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
elseif (isset($_GET[$chat]) && in_array($_GET[$chat], $allowed_chats)) {
    
    $chat =  $_GET[$chat];
    
    $chatArray = array($new, $to_user, $from_user);

    $new = $_GET['new'];
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

    if($_SERVER['CONTENT_TYPE'] !== 'application/json'){
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("Content Type header not set to JSON");
        $response->send();
        exit;
    }

    $rawGetData = file_get_contents('php://input');

    if(!$jsonData = json_decode($rawGetData)){
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("Request body is not valid JSON");
        $response->send();
        exit;
    }

    
    if($new == '' || !is_numeric($new)){
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("new cannot or must be numeric");
        $response->send();
        exit();
    }

    $chat = $jsonData->chat;
    $to_user = $jsonData->to_user;
    $from_user = $jsonData->from_user;

    if($_SERVER['REQUEST_METHOD'] === 'GET'){

        try {
            $query = $readDB->prepare('SELECT * FROM im_msg WHERE is_new = :chat AND to_user = :to_user AND from_user = :from_user AND system = 0');
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

    } else {
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

} elseif (array_key_exists("page", $_GET)){
   
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

        $limitPerPage = 100; 

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
    
} elseif (empty($_GET)){  
    if($_SERVER['REQUEST_METHOD'] === 'GET') {

        if($_SERVER['CONTENT_TYPE'] !== 'application/json'){
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage("Content Type header not set to JSON");
            $response->send();
            exit;
        }

        $rawGetData = file_get_contents('php://input');


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
        try {
            if($_SERVER['CONTENT_TYPE'] !== 'application/json'){   
                $response = new Response();
                $response->setHttpStatusCode(400);
                $response->setSuccess(false);
                $response->addMessage("Content type header is not set to JSON");
                $response->send();
                exit;
            }
            
            $rawPostData = file_get_contents('php://input');
            
            if(!$jsonData = json_decode($rawPostData)){
                $response = new Response();
                $response->setHttpStatusCode(400);
                $response->setSuccess(false);
                $response->addMessage("Request body is not valid JSON");
                $response->send();
                exit;
            }

            if(
                !isset($jsonData->from_user)            || 
                !isset($jsonData->from_group_id)        || 
                !isset($jsonData->to_user)              || 
                !isset($jsonData->to_group_id)          || 
                !isset($jsonData->group_id)             || 
                !isset($jsonData->born)                 || 
                !isset($jsonData->name)                 || 
                !isset($jsonData->msg)                  || 
                !isset($jsonData->ip)                   || 
                !isset($jsonData->is_new)               || 
                !isset($jsonData->system)               || 
                !isset($jsonData->system_type)          || 
                !isset($jsonData->from_user_deleted)    || 
                !isset($jsonData->to_user_deleted)      || 
                !isset($jsonData->msg_translation)      || 
                !isset($jsonData->send)                 || 
                !isset($jsonData->audio_message_id)     || 
                !isset($jsonData->msg_hash)
            ){
                $response = new Response();
                $response->setHttpStatusCode(400);
                $response->setSuccess(false);
                (!isset($jsonData->from_user)           ?  $response->addMessage("From user not supplied "): false); 
                (!isset($jsonData->from_group_id)       ?  $response->addMessage("From group id not supplied"): false); 
                (!isset($jsonData->to_user)             ?  $response->addMessage("To user not supplied"): false); 
                (!isset($jsonData->to_group_id)         ?  $response->addMessage("To group id not supplied"): false); 
                (!isset($jsonData->group_id)            ?  $response->addMessage("Group id not supplied"): false); 
                (!isset($jsonData->born)                ?  $response->addMessage("Born not supplied"): false); 
                (!isset($jsonData->name)                ?  $response->addMessage("Name not supplied"): false); 
                (!isset($jsonData->msg)                 ?  $response->addMessage("Message not supplied"): false); 
                (!isset($jsonData->ip)                  ?  $response->addMessage("Ip address not supplied"): false); 
                (!isset($jsonData->is_new)              ?  $response->addMessage("Is new not supplied"): false); 
                (!isset($jsonData->system)              ?  $response->addMessage("System not supplied"): false); 
                (!isset($jsonData->system_type)         ?  $response->addMessage("System typ not supplied"): false); 
                (!isset($jsonData->from_user_deleted)   ?  $response->addMessage("From user deleted not supplied"): false); 
                (!isset($jsonData->to_user_deleted)     ?  $response->addMessage("To user deleted not supplied"): false); 
                (!isset($jsonData->msg_translation)     ?  $response->addMessage("Message translated not supplied"): false); 
                (!isset($jsonData->send)                ?  $response->addMessage("Send not supplied"): false); 
                (!isset($jsonData->audio_message_id)    ?  $response->addMessage("Audio message id not supplied"): false); 
                (!isset($jsonData->msg_hash)            ?  $response->addMessage("Message hash  not supplied"): false);               
                $response->send();
                exit;
            }
            
            $newMessage = new ImMsg(
                null
                ,$jsonData->from_user
                ,$jsonData->from_group_id
                ,$jsonData->to_user
                ,$jsonData->to_group_id 
                ,$jsonData->group_id
                ,$jsonData->born
                ,$jsonData->name
                ,$jsonData->msg
                ,$jsonData->ip
                ,$jsonData->is_new
                ,$jsonData->system
                ,$jsonData->system_type
                ,$jsonData->from_user_deleted
                ,$jsonData->to_user_deleted
                ,$jsonData->msg_translation
                ,$jsonData->send
                ,$jsonData->audio_message_id
                ,$jsonData->msg_hash
            );

            $from_user          = $newMessage->getFromUser();
            $from_group         = $newMessage->getFromGroupId();
            $to_user            = $newMessage->getToUser();
            $to_group_id        = $newMessage->getToGroupId();
            $group_id           = $newMessage->getGroupId();
            $born               = $newMessage->getBorn(date("Y-m-d H:i:s"));
            $name               = $newMessage->getName();
            $message            = $newMessage->getMsg();
            $ip                 = $newMessage->getIp(IP::getIp());
            $is_new             = $newMessage->getIsNew();
            $system             = $newMessage->getSystem();
            $system_type        = $newMessage->getSystemType();
            $from_user_deleted  = $newMessage->getFromUserDeleted();
            $to_user_deleted    = $newMessage->getToUserDeleted();
            $msg_translation    = $newMessage->getMsgTranslation();
            $send               = $newMessage->getSend();
            $aduio_message_id   = $newMessage->getAudioMessageId();
            $msg_hash           = $newMessage->getMsgHash();
           
            $query = $writeDB->prepare('INSERT INTO im_msg
            (
                from_user, 
                from_group_id, 
                to_user, 
                to_group_id , 
                group_id,
                born, 
                ip, 
                name, 
                msg, 
                ip, 
                is_new, 
                system, 
                system_type, 
                from_user_deleted, 
                to_user_deleted, 
                msg_translation, 
                send, 
                audio_message_id, 
                msg_hash
            ) VALUES 
            (
                :from_user,
                :from_group_id,
                :to_user,
                :to_group_id ,
                :group_id,
                :born,
                :name,
                :msg,
                :ip,
                :is_new,
                :system,
                :system_type,
                :from_user_deleted,
                :to_user_deleted,
                :msg_translation,
                :send,
                :audio_message_id,
                :msg_hash
            )');

            $query->bindParam(':from_user', $from_user, PDO::PARAM_INT);
            $query->bindParam(':from_group_id', $from_group_id, PDO::PARAM_INT);
            $query->bindParam(':to_user', $to_user, PDO::PARAM_INT);
            $query->bindParam(':to_group_id', $to_group, PDO::PARAM_INT);
            $query->bindParam(':group_id', $group_id, PDO::PARAM_INT);
            $query->bindParam(':born', $born, PDO::PARAM_STR);
            $query->bindParam(':name', $name, PDO::PARAM_STR);
            $query->bindParam(':msg', $msg, PDO::PARAM_STR);
            $query->bindParam(':ip', $ip, PDO::PARAM_STR);
            $query->bindParam(':is_new', $is_new, PDO::PARAM_INT);
            $query->bindParam(':system', $system, PDO::PARAM_INT);
            $query->bindParam(':system_type', $system_type, PDO::PARAM_INT);
            $query->bindParam(':from_user_deleted', $from_user_deleted, PDO::PARAM_INT);
            $query->bindParam(':to_user_deleted', $to_user_deleted, PDO::PARAM_INT);
            $query->bindParam(':msg_translation', $msg_translation, PDO::PARAM_STR);
            $query->bindParam(':send', $send, PDO::PARAM_INT);
            $query->bindParam(':audio_message_id', $audio_message_id, PDO::PARAM_INT);
            $query->bindParam(':msg_hash', $msg_hash, PDO::PARAM_STR);
            $query->execute();

            $rowCount = $query->rowCount();

            if($rowCount == 0){
                $response = new Response();
                $response->setHttpStatusCode(500);
                $response->setSuccess(false);
                $response->addMessage("Failed to create task");
                $response->send();
                exit(); 
            }
        } 
        # Error from server
        catch (UserException $ex){
            $response = new Response();
            $response->setHttpStatusCode(400);
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
            $response->addMessage("Faild to insert task into the database = check submitted data for errors");
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
} else {
    $response = new Response();
    $response->setHttpStatusCode(404);
    $response->setSuccess(false);
    $response->addMessage("Endpoint Not Found ");
    $response->send();
    exit;
}