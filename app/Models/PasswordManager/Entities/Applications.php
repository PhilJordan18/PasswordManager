<?php

namespace Models\PasswordManager\Entities;

use Models\Core\Entity;

class Applications extends Entity
{
    public int $id;
    public string $name;
    public string $link;
    public string $icon;
}