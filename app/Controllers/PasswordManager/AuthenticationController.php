<?php

namespace Controllers\PasswordManager;

use Controllers\Controller;
use DateMalformedStringException;
use Exception;
use Models\Exceptions\FormException;
use Models\PasswordManager\Services\TokenServices;
use Models\PasswordManager\Services\UserServices;
use Random\RandomException;
use Zephyrus\Network\Response;
use Zephyrus\Network\Router\Get;
use Zephyrus\Network\Router\Post;

class AuthenticationController extends Controller
{
    #[Get("/authentication")]
    public function authenticate(): Response
    {
        if (getUserSession()) {
            return $this->redirect('/dashboard');
        }
        return $this->render('PasswordManager/authentication', [
            'title' => 'Connexion / Inscription',
            'login_errors' => [],
            'login_values' => [],
            'register_errors' => [],
            'register_values' => [],
            'register_error' => null
        ]);
    }

    #[Post("/register")]
    public function register(): Response
    {
        $form = $this->buildForm();

        try {
            $user = UserServices::register($form);
            $token = TokenServices::generateToken($user->id);
            setSession($user->id, $token);
            error_log("Session définie: " . json_encode(getUserSession()));

            return $this->redirect('/dashboard');

        } catch (FormException $e) {
            return $this->render('PasswordManager/authentication', [
                'title' => 'Connexion / Inscription',
                'register_errors' => $form->getErrorMessages(),
                'register_values' => $form->getFields(),
                'login_errors' => [],
                'login_values' => [],
                'register_error' => $e->getMessage()
            ]);

        } catch (RandomException $e) {
            return $this->render('PasswordManager/authentication', [
                'title' => 'Connexion / Inscription',
                'register_error' => 'Échec de l\'inscription en raison d\'une erreur interne',
                'register_values' => $form->getFields(),
                'login_errors' => [],
                'login_values' => []
            ]);

        } catch (Exception $e) {
            return $this->render('PasswordManager/authentication', [
                'title' => 'Connexion / Inscription',
                'register_error' => 'Échec de l\'inscription : ' . $e->getMessage(),
                'register_values' => $form->getFields(),
                'login_errors' => [],
                'login_values' => []
            ]);
        }
    }

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     */
    #[Post("/login")]
    public function login(): Response
    {
        error_log("Soumission du formulaire de connexion");
        $form = $this->buildForm();
        try {

            $username = $form->getValue('username');
            $password = $form->getValue('password');

            $user = UserServices::login($username, $password);
            if ($user) {
                $token = TokenServices::generateToken($user->id);
                setSession($user->id, $token);
                return $this->redirect('/dashboard');
            }

            return $this->render('PasswordManager/authentication', [
                'title' => 'Connexion / Inscription',
                'login_error' => 'Identifiants invalides',
                'login_values' => $form->getFields(),
                'register_errors' => [],
                'register_values' => []
            ]);

        } catch (FormException $e) {
            return $this->render('PasswordManager/authentication', [
                'title' => 'Connexion / Inscription',
                'login_errors' => $form->getErrorMessages(),
                'login_values' => $form->getFields(),
                'register_errors' => [],
                'register_values' => []
            ]);
        }
    }
}