<?php

namespace Models\PasswordManager\Brokers;

use Models\PasswordManager\Entities\Applications;
use stdClass;
use Zephyrus\Database\DatabaseBroker;

class ApplicationsBroker extends DatabaseBroker
{
    public function findAll(): array
    {
        return $this->select("SELECT * FROM applications ORDER BY name");
    }

    public function findById(int $id): ?stdClass
    {
        return $this->selectSingle("SELECT * FROM applications WHERE id = ?", [$id]);
    }

    public function findByName(string $name): ?stdClass
    {
        return $this->selectSingle("SELECT * FROM applications WHERE LOWER(name) = LOWER(?)", [$name]);
    }

    public function insert(Applications $app): int
    {
        return $this->selectSingle(
            "INSERT INTO applications(name, link, icon) 
             VALUES(?, ?, ?) 
             RETURNING id",
            [
                $app->name,
                $app->link,
                $app->icon ?? 'public/assets/PasswordManager/apps/default.png'
            ]
        )->id;
    }
}