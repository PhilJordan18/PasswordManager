<?php

namespace Models\PasswordManager\Validators;

use Models\Exceptions\FormException;
use Zephyrus\Application\Form;
use Zephyrus\Application\Rule;

class UserValidator
{
    public static function assert(Form $form): void
    {
        $form->field("username", [
            Rule::required(localize("errors.required"))
        ]);
        $form->field("firstname", [
            Rule::required(localize("errors.required"))
        ]);
        $form->field("lastname", [
            Rule::required(localize("errors.required"))
        ]);
        $form->field("email", [
            Rule::required(localize("errors.required")),
            Rule::email(localize("errors.email"))
        ]);
        $form->field("password", [
            Rule::required(localize("errors.required")),
            Rule::minLength(8, localize("errors.password.min_length")),
            Rule::regex("/[a-z]/", localize("errors.password.lowercase")),
            Rule::regex("/[A-Z]/", localize("errors.password.uppercase")),
            Rule::regex("/[0-9]/", localize("errors.password.digit"))
        ]);
        $form->field("confirmPassword", [
            Rule::required(localize("errors.required")),
            new Rule(
                fn($value) => $value === $form->getValue('password'),
                localize("errors.password.mismatch")
            )
        ]);

        if (!$form->verify()) {
            throw new FormException($form);
        }
    }

    public static function assertLogin(Form $form): void
    {
        $form->field("username", [
            Rule::required(localize("errors.required"))
        ]);
        $form->field("password", [
            Rule::required(localize("errors.required")),
            Rule::minLength(8, localize("errors.password.min_length")),
            Rule::regex("/[a-z]/", localize("errors.password.lowercase")),
            Rule::regex("/[A-Z]/", localize("errors.password.uppercase")),
            Rule::regex("/[0-9]/", localize("errors.password.digit"))
        ]);

        if (!$form->verify()) {
            throw new FormException($form);
        }
    }

    public static function assertProfileUpdate(Form $form, int $userId): void
    {
        $form->field("firstname", [
            Rule::required(localize("errors.required"))
        ]);
        $form->field("lastname", [
            Rule::required(localize("errors.required"))
        ]);
        $form->field("email", [
            Rule::required(localize("errors.required")),
            Rule::email(localize("errors.email"))
        ]);
        $form->field("username", [
            Rule::required(localize("errors.required"))
        ]);

        if (!$form->verify()) {
            throw new FormException($form);
        }
    }

    public static function assertPasswordUpdate(Form $form): void
    {
        $form->field("old_password", [
            Rule::required(localize("errors.required"))
        ]);
        $form->field("new_password", [
            Rule::required(localize("errors.required")),
            Rule::minLength(8, localize("errors.password.min_length")),
            Rule::regex("/[a-z]/", localize("errors.password.lowercase")),
            Rule::regex("/[A-Z]/", localize("errors.password.uppercase")),
            Rule::regex("/[0-9]/", localize("errors.password.digit"))
        ]);

        if (!$form->verify()) {
            throw new FormException($form);
        }
    }
}