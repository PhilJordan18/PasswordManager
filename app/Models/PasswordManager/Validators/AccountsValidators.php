<?php

namespace Models\PasswordManager\Validators;

use Models\Exceptions\FormException;
use Zephyrus\Application\Form;
use Zephyrus\Application\Rule;

class AccountsValidators
{
    public static function assert(Form $form): void {
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
}
