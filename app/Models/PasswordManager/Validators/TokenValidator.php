<?php

namespace Models\PasswordManager\Validators;

use Models\Exceptions\FormException;
use Zephyrus\Application\Form;
use Zephyrus\Application\Rule;

class TokenValidator
{
    public static function assert(Form $form): void
    {
        $form->field("user_id", [
            Rule::required(localize("errors.required")),
        ]);
        $form->field("token", [
            Rule::required(localize("errors.required")),
        ]);
        $form->field("expires_at", [
            Rule::required(localize("errors.required")),
        ]);

        if (!$form->verify()) {
            throw new FormException($form);
        }
    }
}