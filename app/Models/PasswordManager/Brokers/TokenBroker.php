<?php

namespace Models\PasswordManager\Brokers;

use Models\PasswordManager\Entities\Token;
use stdClass;
use Zephyrus\Database\DatabaseBroker;

class TokenBroker extends DatabaseBroker
{
    public function findAll(): array
    {
        return $this->select("SELECT * FROM tokens");
    }

    public function findById(int $tokenId): ?stdClass
    {
        return $this->selectSingle("SELECT * FROM tokens WHERE id = ?", [$tokenId]);
    }

    public function insert(Token $token): int
    {
        return $this->selectSingle("INSERT INTO tokens(user_id, token, expires_at) 
                                               VALUES (?, ?, ?) RETURNING id", [
            $token->user_id,
            $token->token,
            $token->expires_at
        ])->id;
    }

    public function update(Token $old, Token $new): int
    {
        $this->query("UPDATE tokens 
                               SET user_id = ?, token = ?, expires_at = ?
                             WHERE id = ?", [
            $new->user_id,
            $new->token,
            $new->expires_at,
            $old->id
        ]);
        return $this->getLastAffectedCount();
    }

    public function delete(Token $token): int
    {
        $this->query("DELETE FROM tokens WHERE id = ?", [$token->id]);
        return $this->getLastAffectedCount();
    }

    public function findByValue(string $token): ?stdClass
    {
        return $this->selectSingle("SELECT * FROM tokens WHERE token = ?", [$token]);
    }
}