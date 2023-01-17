<?php

class ImOpenException extends Exception{ };
class ImOpen{
    private $_id;
    private $_from_user;
    private $_from_group_id	;
    private $_to_user;
    private $_to_group_id;
    private $_group_id;
    private $_mid;
    private $_im_open_visible;
    private $_is_new_msg;
    private $_x;
    private $_y;
    private $_z;
    private $_session;
    private $_session_date;
    private $_last_writing;

    public function __construct($id, $from_user, $from_group_id, $to_user, $to_group_id, $group_id, $mid, $im_open_visible, $is_new_msg, $x, $y, $z, $session, $session_date, $last_writing)
    {
        $this->setId($id);
        $this->setFromUser($from_user);
        $this->setFromGroupId($from_group_id);
        $this->setFromUser($to_user);
        $this->setFromGroupId($to_group_id);
        $this->setFromGroupId($group_id);
        $this->setMid($mid);
        $this->setImOpenVisible($im_open_visible);
        $this->setIsNewMsg($is_new_msg);
        $this->setX($x);
        $this->setY($y);
        $this->setZ($z);
        $this->setSession($session);
        $this->setSessionDate($session_date);
        $this->setLastWriting($last_writing);
    }

    public function getId()
    {
        return $this->_id;
    }

    public function setId($id)
    {
        $this->_id = $id;
    }

    public function getFromUser()
    {
        return $this->_from_user;
    }

    public function setFromUser($from_user)
    {
        $this->_from_user = $from_user;
    }

    public function getFromGroupId()
    {
        return $this->_from_group_id;
    }

    public function setFromGroupId($from_group_id)
    {
        $this->_from_group_id = $from_group_id;
    }

    public function getToUser()
    {
        return $this->_to_user;
    }

    public function setToUser($to_user)
    {
        $this->_to_user = $to_user;
    }

    public function getToGroupId()
    {
        return $this->_to_group_id;
    }

    public function setToGroupId($to_group_id)
    {
        $this->_to_group_id = $to_group_id;
    }

    public function getGroupId()
    {
        return $this->_group_id;
    }

    public function setGroupId($group_id)
    {
        $this->_group_id = $group_id;
    }

    public function getMid()
    {
        return $this->_mid;
    }

    public function setMid($mid)
    {
        $this->_mid = $mid;
    }

    public function getImOpenVisible()
    {
        return $this->_im_open_visible;
    }

    public function setImOpenVisible($im_open_visible)
    {
        $this->_im_open_visible = $im_open_visible;
    }

    public function getIsNewMsg()
    {
        return $this->_is_new_msg;
    }

    public function setIsNewMsg($is_new_msg)
    {
        $this->_is_new_msg = $is_new_msg;
    }

    public function getX()
    {
        return $this->_x;
    }

    public function setX($x)
    {
        $this->_x = $x;
    }

    public function getY()
    {
        return $this->_y;
    }

    public function setY($y)
    {
        $this->_y = $y;
    }

    public function getZ()
    {
        return $this->_z;
    }

    public function setZ($z)
    {
        $this->_z = $z;
    }

    public function getSession()
    {
        return $this->_session;
    }

    public function setSession($session)
    {
        $this->_session = $session;
    }

    public function getSessionDate()
    {
        return $this->_session_date;
    }

    public function setSessionDate($session_date)
    {
        $this->_session_date = $session_date;
    }

    public function getLastWriting()
    {
        return $this->_last_writing;
    }

    public function setLastWriting($last_writing)
    {
        $this->_last_writing = $last_writing;
    }

    public function returnImOpenAsArray(){
        $imopen = array();
        $imopen['id'] = $this->getId();
        $imopen['from_user'] = $this->getFromUser();
        $imopen['from_group_id'] = $this->getGroupId();
        $imopen['to_user'] = $this->getToUser();
        $imopen['to_group_id'] = $this->getToGroupId();
        $imopen['group_id'] = $this->getGroupId();
        $imopen['mid'] = $this->getMid();
        $imopen['im_open_visible'] = $this->getImOpenVisible();
        $imopen['is_new_msg'] = $this->getIsNewMsg();
        $imopen['x'] = $this->getX();
        $imopen['y'] = $this->getY();
        $imopen['z'] = $this->getZ();
        $imopen['session'] = $this->getSession();
        $imopen['session_date'] = $this->getSessionDate();
        $imopen['last_writing'] = $this->getLastWriting();
        return $imopen;
    }
}