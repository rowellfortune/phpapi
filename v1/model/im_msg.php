<?php
class ImMsgException extends Exception{ };

class ImMsg{
    private $_id;
    private $_from_user;
    private $_from_group_id;
    private $_to_user;
    private $_to_group_id;
    private $_group_id;
    private $_born;
    private $_name;
    private $_msg;
    private $_ip;
    private $_is_new;
    private $_system;
    private $_system_type;
    private $_from_user_deleted;
    private $_to_user_deleted;
    private $_msg_translation;
    private $_send;
    private $_audio_message_id;
    private $_msg_hash;

    public function __construct($id, $from_user, $from_group_id, $to_user, $to_group_id, $group_id, $born, $name, $msg, $ip, $is_new, $system, $system_type, $from_user_deleted, $to_user_deleted, $msg_translation, $send, $audio_message_id, $msg_hash)
    {
        $this->setId($id);
        $this->setFromUser($from_user);
        $this->setFromGroupId($from_group_id);
        $this->setToUser($to_user);
        $this->setToGroupId($to_group_id);
        $this->setGroupId($group_id);
        $this->setBorn($born);
        $this->setName($name);
        $this->setMsg($msg);
        $this->setIp($ip);
        $this->setIsNew($is_new);
        $this->setSystem($system);
        $this->setSystemType($system_type);
        $this->setFromUserDeleted($from_user_deleted);
        $this->setToUserDeleted($to_user_deleted);
        $this->setMsgTranslation($msg_translation);
        $this->setSend($send);
        $this->setAudioMessageId($audio_message_id);
        $this->setMsgHash($msg_hash);
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

    public function getBorn()
    {
        return $this->_born;
    }

    public function setBorn($born)
    {
        $this->_born = $born;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function setName($name)
    {
        $this->_name = $name;
    }

    public function getMsg()
    {
        return $this->_msg;
    }

    public function setMsg($msg)
    {
        $this->_msg = $msg;
    }

    public function getIp()
    {
        return $this->_ip;
    }

    public function setIp($ip)
    {
        $this->_ip = $ip;
    }

    public function getIsNew()
    {
        return $this->_is_new;
    }

    public function setIsNew($is_new)
    {
        $this->_is_new = $is_new;
    }

    public function getSystem()
    {
        return $this->_system;
    }

    public function setSystem($system)
    {
        $this->_system = $system;
    }

    public function getSystemType()
    {
        return $this->_system_type;
    }

    public function setSystemType($system_type)
    {
        $this->_system_type = $system_type;
    }

    public function getFromUserDeleted()
    {
        return $this->_from_user_deleted;
    }

    public function setFromUserDeleted($from_user_deleted)
    {
        $this->_from_user_deleted = $from_user_deleted;
    }

    public function getToUserDeleted()
    {
        return $this->_to_user_deleted;
    }

    public function setToUserDeleted($to_user_deleted)
    {
        $this->_to_user_deleted = $to_user_deleted;
    }

    public function getMsgTranslation()
    {
        return $this->_msg_translation;
    }

    public function setMsgTranslation($msg_translation)
    {
        $this->_msg_translation = $msg_translation;
    }

    public function getSend()
    {
        return $this->_send;
    }

    public function setSend($send)
    {
        $this->_send = $send;
    }

    public function getAudioMessageId()
    {
        return $this->_audio_message_id;
    }

    public function setAudioMessageId($audio_message_id)
    {
        $this->_audio_message_id = $audio_message_id;
    }

    public function getMsgHash()
    {
        return $this->_msg_hash;
    }

    public function setMsgHash($msg_hash)
    {
        $this->_msg_hash = $msg_hash;
    }

    public function returnImMsgAsArray()
    {
        $immsg = array();
        $immsg['id'] = $this->getId();
        $immsg['from_user'] = $this->getFromUser();
        $immsg['from_group_id'] = $this->getFromGroupId();
        $immsg['to_user'] = $this->getToUser();
        $immsg['to_group_id'] = $this->getToGroupId();
        $immsg['group_id'] = $this->getGroupId();
        $immsg['born'] = $this->getBorn();
        $immsg['name'] = $this->getName();
        $immsg['msg'] = $this->getMsg();
        $immsg['ip'] = $this->getIp();
        $immsg['is_new'] = $this->getIsNew();
        $immsg['system'] = $this->getSystem();
        $immsg['system_type'] = $this->getSystemType();
        $immsg['from_user_deleted'] = $this->getFromUserDeleted();
        $immsg['to_user_deleted'] = $this->getToUserDeleted();
        $immsg['msg_translation'] = $this->getMsgTranslation();
        $immsg['send'] = $this->getSend();
        $immsg['audio_message_id'] = $this->getAudioMessageId();
        $immsg['msg_hash'] = $this->getMsgHash();
        return $immsg;
    }
}