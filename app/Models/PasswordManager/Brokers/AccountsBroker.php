<?php

namespace Models\PasswordManager\Brokers;

use Models\PasswordManager\Entities\Accounts;
use Zephyrus\Database\DatabaseBroker;

class AccountsBroker extends DatabaseBroker
{
    public function findAll(): array
    {
        return $this->select("SELECT * FROM accounts");
    }

    public function findAllByUserId(int $userId): array
    {
        return $this->select(
            "SELECT a.*, app.name as app_name, app.icon as app_icon 
             FROM accounts a
             JOIN applications app ON a.app_id = app.id
             WHERE a.user_id = ?
             ORDER BY app.name",
            [$userId]
        );
    }

    public function findById(int $id): ?stdClass
    {
        return $this->selectSingle(
            "SELECT a.*, app.name as app_name, app.icon as app_icon 
             FROM accounts a
             JOIN applications app ON a.app_id = app.id
             WHERE a.id = ?",
            [$id]
        );
    }

    public function insert(Accounts $account): int
    {
        return $this->selectSingle(
            "INSERT INTO accounts(user_id, app_id, username, password, last_updated)
             VALUES(?, ?, ?, ?, NOW())
             RETURNING id",
            [
                $account->user_id,
                $account->app_id,
                $account->username,
                $account->password
            ]
        )->id;
    }

    public function exists(int $userId, int $appId, string $username): bool
    {
        $result = $this->selectSingle(
            "SELECT 1 FROM accounts 
             WHERE user_id = ? AND app_id = ? AND username = ?",
            [$userId, $appId, $username]
        );
        return $result !== null;
    }
}