<?php
class UserException extends Exception{ };
class Users {
    private $_id;
    private $_login;
    private $_enemy;
    private $_nowX;
    private $_x0;
    private $_ingame;
    private $_time_in;
    private $_gender;
    private $_angle;
    private $_sila;
    private $_upal;
    private $_popal;
    private $_active;
    private $_zernoX;

    public function __construct($id, $login, $enemy, $nowX, $x0, $ingame, $time_in, $gender, $angle, $sila, $upal, $popal, $active, $zernoX)
    {
        $this->setId($id);
        $this->setLogin($login);
        $this->setEnemy($enemy);
        $this->setNowX($nowX);
        $this->setX0($x0);
        $this->setIngame($ingame);
        $this->setTimeIn($time_in);
        $this->setGender($gender);
        $this->setAngle($angle);
        $this->setSila($sila);
        $this->setUpal($upal);
        $this->setpopal($popal);
        $this->setActive($active);
        $this->setZernoX($zernoX);
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->_id = $id;
    }

    /**
     * @return mixed
     */
    public function getLogin()
    {
        return $this->_login;
    }

    /**
     * @param mixed $login
     */
    public function setLogin($login)
    {
        $this->_login = $login;
    }

    /**
     * @return mixed
     */
    public function getEnemy()
    {
        return $this->_enemy;
    }

    /**
     * @param mixed $enemy
     */
    public function setEnemy($enemy)
    {
        $this->_enemy = $enemy;
    }

    /**
     * @return mixed
     */
    public function getNowX()
    {
        return $this->_nowX;
    }

    /**
     * @param mixed $nowX
     */
    public function setNowX($nowX)
    {
        $this->_nowX = $nowX;
    }

    /**
     * @return mixed
     */
    public function getX0()
    {
        return $this->_x0;
    }

    /**
     * @param mixed $x0
     */
    public function setX0($x0)
    {
        $this->_x0 = $x0;
    }

    /**
     * @return mixed
     */
    public function getIngame()
    {
        return $this->_ingame;
    }

    /**
     * @param mixed $ingame
     */
    public function setIngame($ingame)
    {
        $this->_ingame = $ingame;
    }

    /**
     * @return mixed
     */
    public function getTimeIn()
    {
        return $this->_time_in;
    }

    /**
     * @param mixed $time_in
     */
    public function setTimeIn($time_in)
    {
        $this->_time_in = $time_in;
    }

    /**
     * @return mixed
     */
    public function getGender()
    {
        return $this->_gender;
    }

    /**
     * @param mixed $gender
     */
    public function setGender($gender)
    {
        $this->_gender = $gender;
    }

    /**
     * @return mixed
     */
    public function getAngle()
    {
        return $this->_angle;
    }

    /**
     * @param mixed $angle
     */
    public function setAngle($angle)
    {
        $this->_angle = $angle;
    }

    /**
     * @return mixed
     */
    public function getSila()
    {
        return $this->_sila;
    }

    /**
     * @param mixed $sila
     */
    public function setSila($sila)
    {
        $this->_sila = $sila;
    }

    /**
     * @return mixed
     */
    public function getUpal()
    {
        return $this->_upal;
    }

    /**
     * @param mixed $upal
     */
    public function setUpal($upal)
    {
        $this->_upal = $upal;
    }

    /**
     * @return mixed
     */
    public function getPopal()
    {
        return $this->_popal;
    }

    /**
     * @param mixed $popal
     */
    public function setPopal($popal)
    {
        $this->_popal = $popal;
    }

    /**
     * @return mixed
     */
    public function getActive()
    {
        return $this->_active;
    }

    /**
     * @param mixed $active
     */
    public function setActive($active)
    {
        $this->_active = $active;
    }

    /**
     * @return mixed
     */
    public function getZernoX()
    {
        return $this->_zernoX;
    }

    /**
     * @param mixed $zernoX
     */
    public function setZernoX($zernoX)
    {
        $this->_zernoX = $zernoX;
    }


    public function returnUsersAsArray(){
        $users = array();
        $users['id'] = $this->getId();
        $users['login'] = $this->getLogin();
        $users['enemy'] = $this->getEnemy();
        $users['nowX'] = $this->getnowX();
        $users['name'] = $this->getX0();
        $users['name_seo'] = $this->getingame();
        $users['gender'] = $this->getGender();
        $users['angel'] = $this->getAngle();
        $users['sila'] = $this->getSila();
        $users['upal'] = $this->getUpal();
        $users['active'] = $this->getActive();
        $users['zernox'] = $this->getZernoX();
        return $users;
    }

}