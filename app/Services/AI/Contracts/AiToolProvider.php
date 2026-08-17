<?php

namespace App\Services\AI\Contracts;

interface AiToolProvider
{
    public function moduleAlias(): string;

    public function tools(): array;

    public function execute(string $toolName, array $args, string $tenantId): mixed;
}
