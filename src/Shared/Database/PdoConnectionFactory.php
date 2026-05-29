<?php

declare(strict_types=1);

namespace App\Shared\Database;

use PDO;

final readonly class PdoConnectionFactory
{
    public function __construct(private string $databasePath)
    {
    }

    public function create(): PDO
    {
        $pdo = new PDO('sqlite:' . $this->databasePath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return $pdo;
    }
}
