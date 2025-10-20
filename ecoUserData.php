<?php
/*
 * Class ecoUserData
 *
 * This class models an ecoUser’s data, utilising protected fields for data encapsulation.
 * It takes a database row (array) as input in the constructor and assigns values to private fields, which include:
 * - id
 * - username
 * - password
 * - userType
  */
class ecoUserData
{
    private $_id, $_username, $_password, $_userType;

    public function __construct($dbRow)
    {
        $this->_id = $dbRow['id'];
        $this->_username = $dbRow['username'];
        $this->_password = $dbRow['password'];
        $this->_userType = $dbRow['userType'];
    }

    public function getId()
    {
        return $this->_id;
    }

    public function getUsername()
    {
        return $this->_username;
    }

    public function getPassword()
    {
        return $this->_password;
    }

    public function getUserType()
    {
        return $this->_userType;
    }
}


