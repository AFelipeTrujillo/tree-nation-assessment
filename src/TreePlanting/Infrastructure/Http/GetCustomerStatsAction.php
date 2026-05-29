<?php

declare(strict_types=1);

namespace App\TreePlanting\Infrastructure\Http;

use App\TreePlanting\Application\GetCustomerStats;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class GetCustomerStatsAction
{
    public function __construct(
        private GetCustomerStats $getCustomerStats,
        private CustomerStatsPresenter $presenter,
    ) {
    }

    /**
     * @param array<string, string> $args
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $customerId = $args['customerId'] ?? '';
        $stats = ($this->getCustomerStats)($customerId);

        if ($stats === null) {
            return JsonResponse::write($response, ['error' => 'Customer not found.'], 404);
        }

        return JsonResponse::write($response, $this->presenter->present($stats));
    }
}
