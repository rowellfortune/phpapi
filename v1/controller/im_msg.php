<?php

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
} elseif (array_key_exists("new", $_GET)) {
    $new =  $_GET['new'];

    if($new == '' || !is_numeric($new)){
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("User ID cannot or must be numeric");
        $response->send();
        exit();
    }

    if($_SERVER['REQUEST_METHOD'] === 'GET'){
        try {
            $query = $readDB->prepare('SELECT * FROM im_msg WHERE is_new = :new AND system = 0');
            $query->bindParam(':new', $new, PDO::PARAM_INT);
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
} elseif (array_key_exists("user_id", $_GET)) {
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
            JOIN user as U2 on U2.user_id = M.to_user
            WHERE U.user_id = :user_id limit :pglimit offset :offset');
            $query->bindParam(':pglimit', $limitPerPage, PDO::PARAM_INT);
            $query->bindParam(':offset', $offset, PDO::PARAM_INT);
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
    $page = $_GET['page'];

    if($_SERVER['REQUEST_METHOD'] === 'GET'){

        // $page = $_GET['page'];

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
            JOIN user as U2 on U2.user_id = M.to_user  WHERE U.user_id = :user_id limit :pglimit offset :offset');
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
    
    if($_SERVER['REQUEST_METHOD'] === 'GET'){
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
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
    }
    else {
        $response = new Response();
        $response->setHttpStatusCode(405);
        $response->setSuccess(false);
        $response->addMessage("Request method not allowed");
        $response->send();
        exit;
    }
}else {
    $response = new Response();
    $response->setHttpStatusCode(404);
    $response->setSuccess(false);
    $response->addMessage("Endpoint Not Found ");
    $response->send();
    exit;
}