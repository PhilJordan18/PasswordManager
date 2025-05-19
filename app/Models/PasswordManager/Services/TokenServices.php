<?php

namespace Models\PasswordManager\Services;

use DateMalformedStringException;
use DateTime;
use Models\PasswordManager\Brokers\TokenBroker;
use Models\PasswordManager\Entities\Token;
use Models\PasswordManager\Validators\TokenValidator;
use Random\RandomException;
use Zephyrus\Application\Form;

class TokenServices
{
    public static function readAll(): array
    {
        return Token::buildArray(new TokenBroker()->findAll());
    }

    public static function read(int $tokenId): ?Token
    {
        return Token::build(new TokenBroker()->findById($tokenId));
    }

    public static function insert(Form $form): Token
    {
        TokenValidator::assert($form);
        $broker = new TokenBroker();
        $tokenId = $broker->insert(Token::build($form->buildObject()));
        return self::read($tokenId);
    }

    public static function update(Token $token, Form $form): Token
    {
        TokenValidator::assert($form);
        $broker = new TokenBroker();
        $broker->update($token, Token::build($form->buildObject()));
        return self::read($token->id);
    }

    public static function remove(Token $token): int
    {
        return new TokenBroker()->delete($token);
    }

    public static function getToken(string $token): ?Token {
        $broker = new TokenBroker();
        $tokenValue = $broker->findByValue($token);

        return $tokenValue ? Token::build($tokenValue) : null;
    }

    /**
     * Algorithme Token généré via ChatGPT
     * @throws DateMalformedStringException
     * @throws RandomException
     */
    public static function createToken(int $userId): Token
    {
        $tokenValue = bin2hex(random_bytes(32));
        $expiresAt = new DateTime()->modify('+1 hour')->format('Y-m-d H:i:s');

        $tokenEntity = new Token();
        $tokenEntity->user_id = $userId;
        $tokenEntity->token = $tokenValue;
        $tokenEntity->expires_at = $expiresAt;

        $broker = new TokenBroker();
        $tokenId = $broker->insert($tokenEntity);

        return self::read($tokenId);
    }

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     */
    public static function generateToken(int $userId): string
    {
        $tokenEntity = self::createToken($userId);
        return $tokenEntity->token;
    }
}