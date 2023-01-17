<?php
class UserException extends Exception{ };
class AdminLogin {
    private $id;
    private $time;
    private $ip;
    private $success;

    public function __construct($id, $time, $ip, $success)
    {
        $this->setId($id);
        $this->setTime($time);
        $this->setIp($ip);
        $this->setSuccess($success);
    }
    public function getId()
    {
        return $this->id;
    }
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
    public function getTime()
    {
        return $this->time;
    }
    public function setTime($time)
    {
        $this->time = $time;
        return $this;
    }
    public function getIp()
    {
        return $this->ip;
    }
    public function setIp($ip)
    {
        $this->ip = $ip;
        return $this;
    }
    public function getSuccess()
    {
        return $this->success;
    }
    public function setSuccess($success)
    {
        $this->success = $success;
        return $this;
    }
    public function returnAdminLoginAsArray(){
        $adminlogin = array();
        $adminlogin['id'] = $this->getId();
        $adminlogin['time'] = $this->getTime();
        $adminlogin['ip'] = $this->getIp();
        $adminlogin['success'] = $this->getSuccess();
        return $adminlogin;
    }
}