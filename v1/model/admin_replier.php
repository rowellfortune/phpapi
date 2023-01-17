<?php
class UserException extends Exception{ };
class AdminReplier {
    private $id;
    private $username;
    private $password;
    private $created_at;
    private $updated_at;

    public function __construct($id, $username, $password, $created_at, $updated_at)
    {
        $this->setId($id);
        $this->setUsername($username);
        $this->setPassword($password);
        $this->setCreatedAt($created_at);
        $this->setUpdatedAt($updated_at);
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
    public function getUsername()
    {
        return $this->username;
    }
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }
    public function getPassword()
    {
        return $this->password;
    }
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }
    public function getCreatedAt()
    {
        return $this->created_at;
    }
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
        return $this;
    }
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }
    public function setUpdatedAt($updated_at)
    {
        $this->updated_at = $updated_at;
        return $this;
    }

    public function returnAdminReplierAsArray(){
        $adminreplier = array();
        $adminreplier['id'] = $this->getId();
        $adminreplier['username'] = $this->getUsername();
        $adminreplier['password'] = $this->getPassword();
        $adminreplier['created_at'] = $this->getCreatedAt();
        $adminreplier['updated_at'] = $this->getUpdatedAt();
        return $adminreplier;
    }
}