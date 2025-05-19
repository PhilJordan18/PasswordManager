<?php

namespace Controllers\PasswordManager;

use Controllers\Controller;
use DateMalformedStringException;
use Exception;
use Models\Exceptions\FormException;
use Models\PasswordManager\Services\TokenServices;
use Models\PasswordManager\Services\UserServices;
use Models\PasswordManager\Validators\UserValidator;
use Random\RandomException;
use Zephyrus\Application\Form;
use Zephyrus\Core\Session;
use Zephyrus\Network\Response;
use Zephyrus\Network\Router\Get;
use Zephyrus\Network\Router\Post;

class AuthenticationController extends Controller
{
    #[Get("/authentication")]
    public function authenticate(): Response
    {
        error_log("Accès à /authentication, session: " . json_encode(getUserSession()));
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
        error_log("Soumission du formulaire d'inscription");
        $form = $this->buildForm();
        error_log("Données du formulaire: " . json_encode($form->getFields()));

        try {
            UserValidator::assert($form);

            $user = UserServices::register($form);
            $token = TokenServices::generateToken($user->id);
            setSession($user->id, $token);
            error_log("Session définie: " . json_encode(getUserSession()));

            return $this->redirect('/dashboard');

        } catch (FormException $e) {
            error_log("Erreur de validation: " . json_encode($form->getErrorMessages()));
            return $this->render('PasswordManager/authentication', [
                'title' => 'Connexion / Inscription',
                'register_errors' => $form->getErrorMessages(),
                'register_values' => $form->getFields(),
                'login_errors' => [],
                'login_values' => [],
                'register_error' => $e->getMessage()
            ]);

        } catch (RandomException $e) {
            error_log("Erreur RandomException: " . $e->getMessage());
            return $this->render('PasswordManager/authentication', [
                'title' => 'Connexion / Inscription',
                'register_error' => 'Échec de l\'inscription en raison d\'une erreur interne',
                'register_values' => $form->getFields(),
                'login_errors' => [],
                'login_values' => []
            ]);

        } catch (Exception $e) {
            error_log("Erreur générale: " . $e->getMessage());
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
            UserValidator::assertLogin($form);

            $username = $form->getValue('username');
            $password = $form->getValue('password');

            $user = UserServices::login($username, $password);
            if ($user) {
                $token = TokenServices::generateToken($user->id);
                setSession($user->id, $token);
                error_log("Session définie pour connexion: " . json_encode(getUserSession()));
                return $this->redirect('/dashboard');
            }

            error_log("Échec de la connexion: identifiants invalides");
            return $this->render('PasswordManager/authentication', [
                'title' => 'Connexion / Inscription',
                'login_error' => 'Identifiants invalides',
                'login_values' => $form->getFields(),
                'register_errors' => [],
                'register_values' => []
            ]);

        } catch (FormException $e) {
            error_log("Erreur de validation login: " . json_encode($form->getErrorMessages()));
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