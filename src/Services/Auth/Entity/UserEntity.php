<?php

namespace App\Services\Auth\Entity;

use Controllers\Auth\User;
use Controllers\Entity\Entity;

class UserEntity extends Entity implements User
{

    public $roles;

    public $firstname;

    public $lastname;

    public $username;

    public $password;

    public $email;

    public $sexe;

    public $adress;

    public $typeCarte;

    public $nbrCarte;

    public $published;

    public $createdUp;

    public function getCurrentUser()
    {
        return $this;
    }

    public function getRole()
    {
        $this->roles = json_decode($this->roles)['role'];
        return $this->roles;
    }
}
