<?php

require_once('db.php');
require_once('../model/response.php');

try{
    $writeDB = DB::connectWriteDB();
    $readDB = DB::connectReadDB();
}
catch(PDOException $ex){
    error_log("Connection error - ".$ex, 0);
    $response = new Response();
    $response->setHttpStatusCode(500);
    $response->setSuccess(false);
    $response->addMessage("Database connection error");
    $response->send();
    exit();
}

if(array_key_exists($_GET)){
    $id = $_GET['id'];
    if($id == '' || !is_numeric(id)){}
}
elseif (array_key_exists()){

}