<?php

namespace App\Services\Auth\Entity;

use Controllers\Auth\User;
use Controllers\Entity\Entity;

class LoginEntity extends Entity implements User
{
    public function getCurrentUser()
    {
        return $this;
    }
}
