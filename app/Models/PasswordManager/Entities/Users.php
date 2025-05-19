<?php

namespace Models\PasswordManager\Entities;

use Models\Core\Entity;

class Users extends Entity
{
    public int $id;
    public string $firstname;
    public string $lastname;
    public string $username;
    public string $password;
    public string $email;
    public string $phone_number;
    public string $created_at;
    public string $updated_at;
    public string $master_key;
    public string $private_key;
    public string $public_key;
}