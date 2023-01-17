<?php

require_once('db.php');
require_once('../model/response.php');
require_once('../model/adv_casting.php');

try
{
    $writeDB = DB::connectWriteDB();
    $readDB  = DB::connectReadDB();
}
catch (PDOException $ex){
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
    if ($id == '' || !is_numeric($id)){}
    if($_SERVER['REQUEST_METHOD'] === 'GET'){}
}