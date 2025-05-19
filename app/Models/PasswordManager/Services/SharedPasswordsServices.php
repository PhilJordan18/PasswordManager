<?php

namespace Models\PasswordManager\Services;

use Models\PasswordManager\Brokers\AccountsBroker;
use Models\PasswordManager\Brokers\SharedPasswordsBroker;
use RuntimeException;

class SharedPasswordsServices
{
    public static function sharePassword(int $accountId, int $senderId, int $receiverId, string $senderPassword): void
    {
        $broker = new AccountsBroker();
        $account = $broker->findById($accountId);
        if (!$account || $account->user_id !== $senderId) {
            throw new RuntimeException("Compte invalide ou non autorisé");
        }

        // Récupérer la clé maître du sender
        $sender = UserServices::read($senderId);
        $iv = substr(base64_decode($sender->master_key), 0, 16);
        $encryptedKey = substr(base64_decode($sender->master_key), 16);
        $masterKey = openssl_decrypt($encryptedKey, 'AES-256-CBC', $senderPassword, 0, $iv);

        // Décrypter le mot de passe du compte
        $storedPassword = base64_decode($account->password);
        $iv = substr($storedPassword, 0, 16);
        $encryptedPassword = substr($storedPassword, 16);
        $plainPassword = openssl_decrypt($encryptedPassword, 'AES-256-CBC', $masterKey, 0, $iv);

        // Récupérer la clé publique du receiver
        $receiver = UserServices::read($receiverId);
        $publicKey = $receiver->public_key;

        // Encrypter le mot de passe avec la clé publique
        openssl_public_encrypt($plainPassword, $encryptedSharedPassword, $publicKey);

        // Insérer dans SharedPasswords
        $sharedBroker = new SharedPasswordsBroker();
        $sharedBroker->insert($senderId, $receiverId, $accountId, base64_encode($encryptedSharedPassword));
    }

    public static function retrieveSharedPassword(int $sharedId, int $receiverId, string $receiverPassword): string
    {
        $sharedBroker = new SharedPasswordsBroker();
        $shared = $sharedBroker->findByIdAndReceiver($sharedId, $receiverId);
        if (!$shared) {
            throw new RuntimeException("Partage non trouvé ou non autorisé");
        }

        // Récupérer la clé privée du receiver
        $receiver = UserServices::read($receiverId);
        $iv = substr(base64_decode($receiver->master_key), 0, 16);
        $encryptedKey = substr(base64_decode($receiver->master_key), 16);
        $masterKey = openssl_decrypt($encryptedKey, 'AES-256-CBC', $receiverPassword, 0, $iv);
        $iv = substr(base64_decode($receiver->private_key), 0, 16);
        $encryptedPrivateKey = substr(base64_decode($receiver->private_key), 16);
        $privateKey = openssl_decrypt($encryptedPrivateKey, 'AES-256-CBC', $masterKey, 0, $iv);

        // Décrypter le mot de passe partagé
        $encryptedPassword = base64_decode($shared->encrypted_password);
        openssl_private_decrypt($encryptedPassword, $decryptedPassword, $privateKey);

        return $decryptedPassword;
    }
}