<?php

namespace App\Service\Workflow;

use Symfony\Component\Uid\Uuid;

interface WorkflowInterface
{
    public function getId(): Uuid;

    public function getCurrentState(): string;

    public function getName(): string;

    public function getStamps(): array;

    public function getStatus(): string;

    public function getRetries(): int;
}
