<?php

namespace Models\PasswordManager\Services;

use Models\PasswordManager\Brokers\ApplicationsBroker;
use Models\PasswordManager\Entities\Applications;
use Models\PasswordManager\Validators\UserValidator;
use Zephyrus\Application\Form;

class ApplicationsServices
{
    public static function readAll(): array {
        return Applications::buildArray(new ApplicationsBroker()->findAll());
    }

    public static function read(int $id): ?Applications
    {
        return Applications::build(new ApplicationsBroker()->findById($id));
    }

    public static function getByName(string $name): ?Applications
    {
        return Applications::build(new ApplicationsBroker()->findByName($name));
    }

    public static function appsFactory(string $name): ?Applications {
        if (self::getByName($name)->name === $name) {
            return self::getByName($name);
        } else {
            self::insert();
        }
        return null;
    }

    public static function insert(Form $form): Applications
    {
        UserValidator::assert($form);
        $broker = new ApplicationsBroker();
        $userId = $broker->insert(Applications::build($form->buildObject()));
        return self::read($userId);
    }
}