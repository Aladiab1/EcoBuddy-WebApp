<?php

class ecoUserDataSet {
    protected $id, $password, $username, $userType;

    public function __construct($dbRow) {
        $this->id = $dbRow['id'];
        $this->password = $dbRow['password'];
        $this->username = $dbRow['username'];
        $this->userType = $dbRow['userType'];
    }

    public function getId() {
        return $this->id;
    }
    public function getPassword() {
        return $this->password;

    }
    public function getUsername() {
        return $this->username;
    }
    public function getUserType() {
        return $this->userType;
    }

}
