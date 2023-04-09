<?php
declare(strict_types=1);

namespace App\Service\Workflow;

use Symfony\Component\Uid\Uuid;

interface WorkflowInterface
{
    public function getId(): Uuid;

    public function getCurrentState(): string;

    public function getWorkflowType(): WorkflowType;

    public function getStamps(): array;

    public function getStatus(): string;

    public function getRetries(): int;
}
