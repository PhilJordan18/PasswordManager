<?php namespace Controllers\PasswordManager;

use Controllers\Controller;
use Exception;
use Models\PasswordManager\Services\AccountServices;
use Models\PasswordManager\Services\SharedPasswordsServices;
use Zephyrus\Network\Response;
use Zephyrus\Network\Router\Get;
use Zephyrus\Network\Router\Post;

use Zephyrus\Application\Form;

class DashboardController extends Controller
{
    #[Get("/dashboard")]
    public function index(): Response
    {
        $userId = getSessionId();
        if (!$userId) {
            return $this->redirect('/authentication');
        }

        return $this->render('PasswordManager/dashboard', ['title' => 'Tableau de bord']);
    }

    #[Post("/accounts")]
    public function addAccount(): Response
    {
        $form = new Form($this->request->getParameters());
        $userId = $form->getValue('user_id');
        try {
            $accountId = AccountServices::create($form, $userId);
            return $this->json(['message' => 'Compte ajouté', 'account_id' => $accountId], 201);
        } catch (Exception $e) {
            return jsonError(['error' => $e->getMessage()], 400);
        }
    }

    #[Post("/generate-password")]
    public function generatePassword(): Response
    {
        try {
            $password = AccountServices::generatePassword();
            return $this->json(['password' => $password]);
        } catch (Exception $e) {
            return jsonError(['error' => $e->getMessage()], 500);
        }
    }

    #[Post("/share-password")]
    public function sharePassword(): Response
    {
        $form = new Form($this->request->getParameters());
        $accountId = $form->getValue('account_id');
        $senderId = $form->getValue('sender_id');
        $receiverId = $form->getValue('receiver_id');
        $senderPassword = $form->getValue('sender_password');

        try {
            SharedPasswordsServices::sharePassword($accountId, $senderId, $receiverId, $senderPassword);
            return $this->json(['message' => 'Mot de passe partagé'], 201);
        } catch (Exception $e) {
            return jsonError(['error' => $e->getMessage()], 400);
        }
    }

    #[Get("/shared-password/{sharedId}")]
    public function retrieveSharedPassword(int $sharedId): Response
    {
        $receiverId = $this->request->getParameter('receiver_id');
        $receiverPassword = $this->request->getParameter('receiver_password');

        try {
            $password = SharedPasswordsServices::retrieveSharedPassword($sharedId, $receiverId, $receiverPassword);
            return $this->json(['password' => $password]);
        } catch (Exception $e) {
            return jsonError(['error' => $e->getMessage()], 400);
        }
    }
}