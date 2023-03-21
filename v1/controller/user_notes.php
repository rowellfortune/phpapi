<?php 

require_once('db.php');
require_once('../model/response.php');
require_once('../model/user_notes.php');

try {
    $writeDB = DB::connectWriteDB();
    $readDB = DB::connectReadDB();
} catch (PDOException $ex) {
    error_log("Connection error - ".$ex, 0);
    $response = new Response();
    $response->setHttpStatusCode(500);
    $response->setSuccess(false);
    $response->addMessage("Database connection error");
    $response->send();
    exit();
}

if(array_key_exists("noteid", $_GET)){
    $noteid = $_GET['noteid'];

    if ($taskid == '' || !is_numeric($notekid)) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("Note ID cannot be blank or must be numeric");
        $response->send();
        exit();
    }

    if($_SERVER['REQUEST_METHOD'] === 'GET'){
        try {
            $query = $readDB->prepare('SELECT id, note_catergory, note_content, from user_notes where id = :noteid');
            $query->bindParam(':noteid', $noteid, PDO::PARAM_INT);
            $query->execute();

            $rowCount = $query->rowCount();

            if($rowCount === 0){
                $response = new Response();
                $response->setHttpStatusCode(404);
                $response->setSuccess(false);
                $response->addMessage("Note not found");
                $response->send();
                exit;
            }

            while($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $note = new UserNontes(
                    $row['id'],
                    $row['note_category'],
                    $row['note_content']
                );

                $noteArray[] = $note->returnNoteAsArray();
            }

            $returnData = array();
            $returnData['rows_returned'] = $rowCount;
            $returnData['notes'] = $noteArray;

            ## Success
            $response = new Response();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->toCache(true);
            $response->setData($returnData);
            $response->send();
            exit;
        }
        # Error from server
        catch (TaskException $ex) {
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage($ex->getMessage());
            $response->send();
            exit;
        }

        # Faild to get data from database
        catch (PDOException $ex) {
            error_log("Database query error - ".$ex, 0);
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("Faild to get tasks");
            $response->send();
            exit();
        }
    }
    elseif($_SERVER['REQUEST_METHOD'] === 'DELETE') {

        try {
            $query = $writeDB->prepare('DELETE from user_notes where id = :noteid');
            $query->bindParam(':notekid', $taskid, PDO::PARAM_INT);
            $query->execute();

            $rowCount = $query->rowCount();

            if($rowCount == 0){
                $response = new Response();
                $response->setHttpStatusCode(404);
                $response->setSuccess(false);
                $response->addMessage("Note no found");
                $response->send();
                exit();
            }

            $response = new Response();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->toCache(true);
            $response->addMessage("Note deleted");
            $response->send();
            exit;
        }
        catch (PDOException $ex) {
            error_log("Database query error - ".$ex, 0);
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("Faild to delete note");
            $response->send();
            exit();
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

}elseif (empty($_GET)) {
    
}
else {
    $response = new Response();
    $response->setHttpStatusCode(404);
    $response->setSuccess(false);
    $response->addMessage("Endpoint Not Found ");
    $response->send();
    exit;
}