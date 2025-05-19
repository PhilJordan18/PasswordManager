<?php

namespace Models\PasswordManager\Brokers;

use Zephyrus\Database\DatabaseBroker;

class SharedPasswordsBroker extends DatabaseBroker
{
    public function insert(int $senderId, int $receiverId, int $accountId, string $encryptedPassword): int
    {
        return $this->selectSingle(
            "INSERT INTO SharedPasswords(sender_id, receiver_id, account_id, encrypted_password, created_at)
             VALUES(?, ?, ?, ?, NOW())
             RETURNING id",
            [$senderId, $receiverId, $accountId, $encryptedPassword]
        )->id;
    }

    public function findByIdAndReceiver(int $sharedId, int $receiverId): ?\stdClass
    {
        return $this->selectSingle(
            "SELECT * FROM SharedPasswords WHERE id = ? AND receiver_id = ?",
            [$sharedId, $receiverId]
        );
    }
}