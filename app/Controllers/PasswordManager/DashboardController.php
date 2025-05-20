<?php

namespace Controllers\PasswordManager;

use Controllers\Controller;
use Exception;
use Models\PasswordManager\Services\AccountServices;
use Models\PasswordManager\Services\ApplicationsServices;
use Models\PasswordManager\Services\SharedPasswordsServices;
use Tracy\Debugger;
use Tracy\ILogger;
use Zephyrus\Network\Response;
use Zephyrus\Network\Router\Get;
use Zephyrus\Network\Router\Post;
use Zephyrus\Application\Form;
use Zephyrus\Core\Session;

class DashboardController extends Controller
{
    #[Get("/dashboard")]
    public function index(): Response
    {
        return $this->renderSection('dashboard');
    }

    #[Get("/passwords")]
    public function passwords(): Response
    {
        return $this->renderSection('passwords');
    }

    #[Get("/security")]
    public function security(): Response
    {
        return $this->renderSection('security');
    }

    #[Get("/settings")]
    public function settings(): Response
    {
        return $this->renderSection('settings');
    }

    #[Post("/logout")]
    public function logout(): Response
    {
        try {
            Session::destroy();
            if (is_writable(Debugger::$logDirectory)) {
                Debugger::log('Utilisateur déconnecté', ILogger::INFO);
            }
            return $this->json(['success' => true, 'message' => 'Déconnexion réussie'], 200);
        } catch (Exception $e) {
            if (is_writable(Debugger::$logDirectory)) {
                Debugger::log("Erreur déconnexion: {$e->getMessage()}", ILogger::ERROR);
            }
            return $this->json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    private function renderSection(string $activeSection): Response
    {
        $userId = getSessionId();
        if (!$userId) {
            Debugger::log('Utilisateur non authentifié', ILogger::ERROR);
            return $this->redirect('/authentication');
        }

        // Récupérer les comptes
        $accounts = [];
        try {
            $accounts = AccountServices::getAllForUser($userId, 'Password19.');
        } catch (Exception $e) {
            Debugger::barDump($e->getMessage(), 'Erreur récupération comptes');
        }

        // Récupérer les applications
        $applications = ApplicationsServices::readAll();

        // Statistiques
        $stats = [
            'total' => count($accounts),
            'weak' => count(array_filter($accounts, fn($account) => strlen($account->password) < 8)),
            'medium' => count(array_filter($accounts, fn($account) => strlen($account->password) >= 8 && strlen($account->password) < 12)),
            'strong' => count(array_filter($accounts, fn($account) => strlen($account->password) >= 12))
        ];

        return $this->render('PasswordManager/dashboard', [
            'title' => 'Tableau de bord',
            'nonce' => bin2hex(random_bytes(16)),
            'activeSection' => $activeSection,
            'accounts' => $accounts,
            'applications' => $applications,
            'stats' => $stats
        ]);
    }

    #[Post("/accounts")]
    public function addAccount(): Response
    {
        $userId = getSessionId();
        if (!$userId) {
            return $this->json(['success' => false, 'error' => 'Utilisateur non authentifié'], 401);
        }

        $form = new Form($this->request->getParameters());
        try {
            $accountId = AccountServices::create($form, $userId);
            return $this->json(['success' => true, 'message' => 'Compte ajouté', 'account_id' => $accountId], 201);
        } catch (Exception $e) {
            Debugger::log("Erreur ajout compte: {$e->getMessage()}", ILogger::ERROR);
            return $this->json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    #[Post("/accounts/{id}/update")]
    public function updateAccount(int $id): Response
    {
        $userId = getSessionId();
        if (!$userId) {
            return $this->json(['success' => false, 'error' => 'Utilisateur non authentifié'], 401);
        }

        $form = new Form($this->request->getParameters());
        try {
            AccountServices::update($id, $form, $userId);
            return $this->json(['success' => true, 'message' => 'Compte mis à jour'], 200);
        } catch (Exception $e) {
            Debugger::log("Erreur mise à jour compte: {$e->getMessage()}", ILogger::ERROR);
            return $this->json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    #[Post("/accounts/{id}/delete")]
    public function deleteAccount(int $id): Response
    {
        $userId = getSessionId();
        if (!$userId) {
            return $this->json(['success' => false, 'error' => 'Utilisateur non authentifié'], 401);
        }

        try {
            AccountServices::delete($id, $userId);
            return $this->json(['success' => true, 'message' => 'Compte supprimé'], 200);
        } catch (Exception $e) {
            Debugger::log("Erreur suppression compte: {$e->getMessage()}", ILogger::ERROR);
            return $this->json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    #[Post("/generate-password")]
    public function generatePassword(): Response
    {
        try {
            $password = AccountServices::generatePassword();
            return $this->json(['success' => true, 'password' => $password]);
        } catch (Exception $e) {
            Debugger::log("Erreur génération mot de passe: {$e->getMessage()}", ILogger::ERROR);
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    #[Post("/share-password")]
    public function sharePassword(): Response
    {
        $userId = getSessionId();
        if (!$userId) {
            return $this->json(['success' => false, 'error' => 'Utilisateur non authentifié'], 401);
        }

        $form = new Form($this->request->getParameters());
        $accountId = $form->getValue('account_id');
        $receiverId = $form->getValue('receiver_id');
        $senderPassword = $form->getValue('sender_password');

        try {
            SharedPasswordsServices::sharePassword($accountId, $userId, $receiverId, $senderPassword);
            return $this->json(['success' => true, 'message' => 'Mot de passe partagé'], 201);
        } catch (Exception $e) {
            Debugger::log("Erreur partage mot de passe: {$e->getMessage()}", ILogger::ERROR);
            return $this->json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    #[Get("/shared-password/{sharedId}")]
    public function retrieveSharedPassword(int $sharedId): Response
    {
        $userId = getSessionId();
        if (!$userId) {
            return $this->json(['success' => false, 'error' => 'Utilisateur non authentifié'], 401);
        }

        $receiverPassword = $this->request->getParameter('receiver_password');

        try {
            $password = SharedPasswordsServices::retrieveSharedPassword($sharedId, $userId, $receiverPassword);
            return $this->json(['success' => true, 'password' => $password]);
        } catch (Exception $e) {
            Debugger::log("Erreur récupération mot de passe partagé: {$e->getMessage()}", ILogger::ERROR);
            return $this->json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }
}