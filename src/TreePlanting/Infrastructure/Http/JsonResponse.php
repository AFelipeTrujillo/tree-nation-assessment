<?php

declare(strict_types=1);

namespace App\TreePlanting\Infrastructure\Http;

use Psr\Http\Message\ResponseInterface;

final class JsonResponse
{
    /**
     * @param mixed $data
     */
    public static function write(ResponseInterface $response, mixed $data, int $status = 200): ResponseInterface
    {
        $payload = json_encode($data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
        $response->getBody()->write($payload);

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}
