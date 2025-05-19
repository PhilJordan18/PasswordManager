<?php

namespace Models\PasswordManager\Entities;

class SharedPasswords
{
    public int $id;
    public int $sender_id;
    public int $receiver_id;
    public int $account_id;
    public string $encrypted_password;
    public string $created_at;
}