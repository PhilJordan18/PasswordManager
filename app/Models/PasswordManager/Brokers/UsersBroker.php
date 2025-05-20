<?php

namespace Models\PasswordManager\Brokers;

use Exception;
use Models\PasswordManager\Entities\Users;
use stdClass;
use Zephyrus\Database\DatabaseBroker;
use Zephyrus\Security\Cryptography;

class UsersBroker extends DatabaseBroker
{
    public function findAll(): array
    {
        return $this->select("SELECT * FROM users");
    }

    public function findById(int $id): ?stdClass
    {
        return $this->selectSingle("SELECT * FROM users WHERE id = ?", [$id]);
    }

    /**
     * @throws Exception
     */
    public function insert(Users $user): int
    {
        $hashedPassword = Cryptography::hash($user->password, PASSWORD_BCRYPT);
        $result = $this->selectSingle(
            "INSERT INTO users(firstname, lastname, username, password, email, phone_number, master_key, public_key, private_key, created_at, updated_at)
             VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
             RETURNING id",
            [
                $user->firstname,
                $user->lastname,
                $user->username,
                $hashedPassword,
                $user->email,
                $user->phone_number,
                $user->master_key,
                $user->public_key,
                $user->private_key
            ]
        );
        return $result->id;
    }

    public function update(Users $old, Users $new): int
    {
        $updatedPassword = !empty($new->password) ? Cryptography::hash($new->password, PASSWORD_BCRYPT) : $old->password;
        $this->query(
            "UPDATE users 
             SET firstname = ?, lastname = ?, username = ?, password = ?, email = ?, phone_number = ?, master_key = ?, public_key = ?, private_key = ?, updated_at = NOW()
             WHERE id = ?",
            [
                $new->firstname,
                $new->lastname,
                $new->username,
                $updatedPassword,
                $new->email,
                $new->phone_number,
                $new->master_key,
                $new->public_key,
                $new->private_key,
                $old->id
            ]
        );
        return $this->getLastAffectedCount();
    }

    public function delete(Users $user): int
    {
        $this->query("DELETE FROM users WHERE id = ?", [$user->id]);
        return $this->getLastAffectedCount();
    }

    public function findByName(string $username): ?stdClass
    {
        return $this->selectSingle("SELECT * FROM users WHERE username = ?", [$username]);
    }
}