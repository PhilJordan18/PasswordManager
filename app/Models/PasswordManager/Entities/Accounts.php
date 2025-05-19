<?php

namespace Models\PasswordManager\Entities;

use Models\Core\Entity;

class Accounts extends Entity
{
    public int $id;
    public int $user_id;
    public int $app_id;
    public string $username;
    public string $password;
    public string $last_updated;
}