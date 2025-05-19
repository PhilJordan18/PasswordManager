<?php

namespace Models\PasswordManager\Validators;

use Models\Exceptions\FormException;
use Zephyrus\Application\Form;
use Zephyrus\Application\Rule;

class ApplicationsValidators
{
    public static function assert(Form $form): void
    {
        $form->field("username", [
            Rule::required(localize("errors.required"))
        ]);

        if (!$form->verify()) {
            throw new FormException($form);
        }
    }
}