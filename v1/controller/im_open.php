<?php

require_once('db.php');
require_once('../model/response.php');
require_once('../model/im_open.php');

try {
    $writeDB = DB::connectWriteDB();
    $readDB = DB::connectReadDB();
}
catch (PDOException $ex) {
    error_log("Connection error - ".$ex, 0);
    $response = new Response();
    $response->setHttpStatusCode(500);
    $response->setSuccess(false);
    $response->addMessage("Database connection error");
    $response->send();
    exit();
}

if(array_key_exists("id", $_GET)){
    $id = $_GET['id'];

    if($id == '' || !is_numeric($id)){
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("User ID cannot or must be numeric");
        $response->send();
        exit();
    }

    if($_SERVER['REQUEST_METHOD'] === 'GET'){
        try {
            $query = $readDB->prepare('SELECT *  FROM user WHERE id = :id');
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
                $user = new User(
                    $row['user_id'],
                    $row['partner'],
                    $row['gold_days'],
                    $row['role'],
                    $row['name'],
                    $row['name_seo'],
                    $row['gender'],
                    $row['orientation'],
                    $row['p_orientation'],
                    $row['relation'],
                    $row['couple'],
                    $row['couple_id']
                );

                $userArray[] = $user->returnUserAsArray();
            }

            $returnData = array();
            $returnData['rows_returned'] = $rowCount;
            $returnData['users'] = $userArray;

            $response = new Response();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->toCache(true);
            $response->setData($returnData);
            $response->send();
            exit;
        }
        catch (UserException $ex){
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage($ex->getMessage());
            $response->send();
            exit();
        }
        catch (PDOException $ex){
            error_log("Database query error - ".$ex, 0);
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("Faild to get user");
            $response->send();
            exit();
        }
    }
}

elseif (array_key_exists()){
}