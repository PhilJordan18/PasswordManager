<?php

namespace Models\PasswordManager\Services;

use Models\PasswordManager\Brokers\AccountsBroker;
use Models\PasswordManager\Entities\Accounts;
use Models\PasswordManager\Validators\AccountsValidators;
use Random\RandomException;
use RuntimeException;
use Zephyrus\Application\Form;

class AccountServices
{
    public static function readAll(): array
    {
        return Accounts::buildArray(new AccountsBroker()->findAll());
    }

    public static function read(int $id): ?Accounts
    {
        return Accounts::build(new AccountsBroker()->findById($id));
    }

    public static function getAllForUser(int $userId, string $userPassword): array
    {
        $broker = new AccountsBroker();
        $accounts = $broker->findAllByUserId($userId);
        $user = UserServices::read($userId);
        $iv = substr(base64_decode($user->master_key), 0, 16);
        $encryptedKey = substr(base64_decode($user->master_key), 16);
        $masterKey = openssl_decrypt($encryptedKey, 'AES-256-CBC', $userPassword, 0, $iv);

        foreach ($accounts as &$account) {
            // Décrypter username
            $account->username = base64_decode($account->username);

            // Décrypter password
            $storedPassword = base64_decode($account->password);
            $iv = substr($storedPassword, 0, 16);
            $encryptedPassword = substr($storedPassword, 16);
            $account->password = openssl_decrypt(
                $encryptedPassword,
                'AES-256-CBC',
                $masterKey,
                0,
                $iv
            );
        }

        return $accounts;
    }

    public static function create(Form $form, int $userId): int
    {
        AccountsValidators::assert($form);

        $appId = $form->getValue('app_id');
        if ($form->getValue('app_id') === 'other') {
            $appForm = new Form(['name' => $form->getValue('custom_app_name')]);
            $app = ApplicationsServices::insert($appForm);
            $appId = $app->id;
        }

        $accountsBroker = new AccountsBroker();
        $username = base64_encode($form->getValue('username')); // Encrypter username
        if ($accountsBroker->exists($userId, $appId, $username)) {
            throw new RuntimeException("Ce compte existe déjà pour cette application");
        }

        // Récupérer la clé maître
        $user = UserServices::read($userId);
        $iv = substr(base64_decode($user->master_key), 0, 16);
        $encryptedKey = substr(base64_decode($user->master_key), 16);
        $masterKey = openssl_decrypt($encryptedKey, 'AES-256-CBC', $form->getValue('password'), 0, $iv);

        // Encrypter le mot de passe
        $iv = random_bytes(16);
        $encryptedPassword = openssl_encrypt(
            $form->getValue('password'),
            'AES-256-CBC',
            $masterKey,
            0,
            $iv
        );
        $storedPassword = base64_encode($iv . $encryptedPassword);

        $account = new Accounts();
        $account->user_id = $userId;
        $account->app_id = $appId;
        $account->username = $username;
        $account->password = $storedPassword;

        return $accountsBroker->insert($account);
    }

    public static function insert(Form $form): Accounts
    {
        AccountsValidators::assert($form);
        $broker = new AccountsBroker();
        $userId = $broker->insert(Accounts::build($form->buildObject()));
        return self::read($userId);
    }

    public static function generatePassword(): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()';
        $password = '';
        for ($i = 0; $i < 12; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $password;
    }
}