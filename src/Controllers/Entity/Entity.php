<?php

namespace Controllers\Entity;

class Entity
{
    public $id;
    
    public $created_at;

    public $created_up;

    public $image;

    public $role;

    public $status;

    public $description;

    public $phone;

    public $adress;

    public $published;

    public $createdUp;
    
    public $createAt;

    public $usersId;

    public $username;

    public function getRole()
    {
        if ($this->role) {
            $tab =  json_decode($this->role);
            $role =  explode('_', $tab->role);
            return $role[1];
        }
        return null;
    }


    public function setCreatedAt($dateTime)
    {
        if (is_string($dateTime)) {
            $this->created_at = new \DateTime($dateTime);
        }
    }
}
