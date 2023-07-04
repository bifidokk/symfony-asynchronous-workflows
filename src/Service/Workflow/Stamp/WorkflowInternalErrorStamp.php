<?php
declare(strict_types=1);

namespace App\Service\Workflow\Stamp;

use App\Service\Workflow\WorkflowStampInterface;

class WorkflowInternalErrorStamp implements WorkflowStampInterface
{
    public function __construct(
        private readonly string $message = '',
        private readonly array $payload = [],
    ) {
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }
}
