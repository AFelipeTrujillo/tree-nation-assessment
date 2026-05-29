<?php

declare(strict_types=1);

namespace App\TreePlanting\Infrastructure\Http;

use App\TreePlanting\Application\GetVisitsPerHour;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class GetVisitsPerHourAction
{
    public function __construct(private GetVisitsPerHour $getVisitsPerHour)
    {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return JsonResponse::write($response, ($this->getVisitsPerHour)());
    }
}
