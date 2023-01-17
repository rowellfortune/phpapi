<?php

class UserException extends Exception
{
}

class AdvCars
{
    private $id;
    private $subject;
    private $body;
    private $user_id;
    private $razd_id;
    private $created;
    private $cat_id;
    private $price_id;

    public function __construct($id, $subject, $body, $user_id, $razd_id, $created, $cat_id, $price_id)
    {
        $this->id       = $id;
        $this->subject  = $subject;
        $this->body     = $body;
        $this->user_id  = $user_id;
        $this->razd_id  = $razd_id;
        $this->created  = $created;
        $this->cat_id   = $cat_id;
        $this->price_id = $price_id;
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
    public function getSubject()
    {
        return $this->subject;
    }
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }
    public function getBody()
    {
        return $this->body;
    }
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }
    public function getUserId()
    {
        return $this->user_id;
    }
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
        return $this;
    }
    public function getRazdId()
    {
        return $this->razd_id;
    }
    public function setRazdId($razd_id)
    {
        $this->razd_id = $razd_id;
        return $this;
    }
    public function getCreated()
    {
        return $this->created;
    }
    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
    }
    public function getCatId()
    {
        return $this->cat_id;
    }
    public function setCatId($cat_id)
    {
        $this->cat_id = $cat_id;
        return $this;
    }
    public function getPrice()
    {
        return $this->price;
    }
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }

    public function returnAdminReplierAsArray()
    {
        $advcars = array();
        $advcars['id'] = $this->getId();
        $advcars['subject'] = $this->getSubject();
        $advcars['body'] = $this->getBody();
        $advcars['user_id'] = $this->getUserId();
        $advcars['razd_id'] = $this->getRazdId();
        $advcars['created'] = $this->getCreated();
        $advcars['cat_id'] = $this->getCatId();
        $advcars['price'] = $this->getPrice();
        return $advcars;
    }
}