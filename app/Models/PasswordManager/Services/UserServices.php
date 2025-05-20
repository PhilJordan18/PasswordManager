<?php

namespace Models\PasswordManager\Services;

use Exception;
use Models\PasswordManager\Brokers\UsersBroker;
use Models\PasswordManager\Entities\Users;
use Models\PasswordManager\Validators\UserValidator;
use Random\RandomException;
use Zephyrus\Application\Form;
use Zephyrus\Security\Cryptography;

class UserServices
{
    public static function readAll(): array
    {
        return Users::buildArray(new UsersBroker()->findAll());
    }

    public static function read(int $id): ?Users
    {
        return Users::build(new UsersBroker()->findById($id));
    }

    public static function insert(Form $form): Users
    {
        UserValidator::assert($form);
        $broker = new UsersBroker();
        $userId = $broker->insert(Users::build($form->buildObject()));
        return self::read($userId);
    }

    public static function update(Users $user, Form $form): Users
    {
        UserValidator::assert($form);
        $broker = new UsersBroker();
        $broker->update($user, Users::build($form->buildObject()));
        return self::read($user->id);
    }

    public static function remove(Users $user): int
    {
        return new UsersBroker()->delete($user);
    }

    public static function login(string $username, string $password): ?Users
    {
        $broker = new UsersBroker();
        $userData = $broker->findByName($username);

        if ($userData && password_verify($password, $userData->password)) {
            return Users::build($userData);
        }

        return null;
    }

    /**
     * @throws RandomException
     * @throws Exception
     */
    public static function register(Form $form): Users
    {
        error_log("Début de l'inscription pour username: " . $form->getValue('username'));

        if (self::isAlreadyRegistered($form)) {
            error_log("Échec : utilisateur déjà existant");
            throw new Exception("User already exists");
        }

        $user = Users::build($form->buildObject());
        $password = $form->getValue('password');

        // Générer une clé maître
        try {
            $masterKey = bin2hex(random_bytes(32)); // 256 bits
            $iv = random_bytes(16);
            $user->master_key = base64_encode($iv . openssl_encrypt($masterKey, 'AES-256-CBC', $password, 0, $iv));
            error_log("Clé maître générée");
        } catch (RandomException $e) {
            error_log("Erreur lors de la génération de la clé maître: " . $e->getMessage());
            throw $e;
        }

        // Générer une paire de clés RSA
        try {
            $rsaConfig = [
                "digest_alg" => "sha256",
                "private_key_bits" => 2048,
                "private_key_type" => OPENSSL_KEYTYPE_RSA,
            ];
            $res = openssl_pkey_new($rsaConfig);
            if ($res === false) {
                throw new Exception("Échec de la génération de la clé RSA");
            }
            openssl_pkey_export($res, $privateKey);
            $publicKey = openssl_pkey_get_details($res)['key'];

            $user->public_key = $publicKey;
            $iv = random_bytes(16);
            $user->private_key = base64_encode($iv . openssl_encrypt($privateKey, 'AES-256-CBC', $masterKey, 0, $iv));
            error_log("Clés RSA générées");
        } catch (Exception $e) {
            error_log("Erreur lors de la génération des clés RSA: " . $e->getMessage());
            throw $e;
        }

        $broker = new UsersBroker();
        try {
            $userId = $broker->insert($user);
            error_log("Utilisateur inséré avec ID: " . $userId);
            return self::read($userId);
        } catch (Exception $e) {
            error_log("Erreur lors de l'insertion de l'utilisateur: " . $e->getMessage());
            throw $e;
        }
    }

    private static function isAlreadyRegistered(Form $form): bool
    {
        $broker = new UsersBroker();
        $exists = $broker->findByName($form->getValue('username')) !== null;
        error_log("Vérification de l'existence de l'utilisateur: " . ($exists ? "existe" : "n'existe pas"));
        return $exists;
    }
}