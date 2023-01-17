<?php
class UserException extends Exception{ };
class User {
    private $_user_id;
    private $_partner;
    private $_gold_days;
    private $_role;
    private $_name;
    private $_name_seo;
    private $_orientation;
    private $_p_orientation;
    private $_relation;
    private $_couple;
    private $_couple_id;
    private $_user_type;

    public function __construct(
        $user_id,
        $partner,
        $gold_days,
        $role,
        $name,
        $name_seo,
        $gender,
        $orientation,
        $p_orientation,
        $relation,
        $couple,
        $couple_id,
        $user_type
    ){
        $this->setUserId($user_id);
        $this->setPartner($partner);
        $this->setGoldDays($gold_days);
        $this->setRole($role);
        $this->setName($name);
        $this->setNameSeo($name_seo);
        $this->setGender($gender);
        $this->setOrientation($orientation);
        $this->setPorientation($p_orientation);
        $this->setRelation($relation);
        $this->setCouple($couple);
        $this->setCoupleId($couple_id);
        $this->setUserType($user_type);
    }

    public function getUserId()
    {
        return $this->_user_id;
    }
    public function setUserId($user_id)
    {
        $this->_user_id = $user_id;
    }
    public function getPartner()
    {
        return $this->_partner;
    }
    public function setPartner($partner)
    {
        $this->_partner = $partner;
    }
    public function getGoldDays()
    {
        return $this->_gold_days;
    }
    public function setGoldDays($gold_days)
    {
        $this->_gold_days = $gold_days;
    }
    public function getRole()
    {
        return $this->_role;
    }
    public function setRole($role)
    {
        $this->_role = $role;
    }
    public function getName()
    {
        return $this->_name;
    }
    public function setName($name)
    {
        $this->_name = $name;
    }
    public function getNameSeo()
    {
        return $this->_name_seo;
    }
    public function setNameSeo($name_seo)
    {
        $this->_name_seo = $name_seo;
    }
    public function getGender()
    {
        return $this->_gender;
    }
    public function setGender($gender)
    {
        $this->_gender = $gender;
    }
    public function getOrientation()
    {
        return $this->_orientation;
    }
    public function setOrientation($orientation)
    {
        $this->_orienatation = $orientation;
    }
    public function getPorientation()
    {
        return $this->_p_orientation;
    }
    public function setPorientation($p_orientation)
    {
        $this->_p_orientation = $p_orientation;
    }
    public function getRelation()
    {
        return $this->_relation;
    }
    public function setRelation($relation)
    {
        $this->_relation = $relation;
    }
    public function getCouple()
    {
        return $this->_couple;
    }
    public function setCouple($couple)
    {
        $this->_couple = $couple;
    }
    public function getCoupleId()
    {
        return $this->_couple_id;
    }
    public function setCoupleId($couple_id)
    {
        $this->_couple_id = $couple_id;
    }

    public function getUserType()
    {
        return $this->_user_type;
    }
    public function setUserType($user_type)
    {
        $this->_user_type = $user_type;
    }



    public function returnUserAsArray(){
        $user = array();
        $user['user_id'] = $this->getUserId();
        $user['partner'] = $this->getPartner();
        $user['gold_days'] = $this->getGoldDays();
        $user['role'] = $this->getRole();
        $user['name'] = $this->getName();
        $user['name_seo'] = $this->getNameSeo();
        $user['gender'] = $this->getGender();
        $user['orientation'] = $this->getOrientation();
        $user['p_orientation'] = $this->getPorientation();
        $user['relation'] = $this->getRelation();
        $user['couple'] = $this->getCouple();
        $user['couple_id'] = $this->getCoupleId();
        $user['user_type'] = $this->getUserType();
        return $user;
    }
}