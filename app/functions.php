<?php

use Zephyrus\Network\ContentType;
use Zephyrus\Network\Response;

/**
 * Add global project functions here ...
 */

function setSession(int $userId, string $token): void
{
    $_SESSION['user'] = [
        'id' => $userId,
        'token' => $token
    ];
}

function getSessionId(): ?int
{
    return $_SESSION['user']['id'] ?? null;
}

function getUserSession(): ?array
{
    return $_SESSION['user'] ?? null;
}

function jsonError(mixed $data, int $statusCode = 400): Response
{
    $response = new Response(ContentType::JSON, $statusCode);
    $response->setContent(json_encode($data));
    return $response;
}