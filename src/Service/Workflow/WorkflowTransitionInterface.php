<?php

namespace App\Service\Workflow;

use App\Entity\WorkflowEntry;

interface WorkflowTransitionInterface
{
    public function handle(WorkflowEntry $workflowEntry): void;

    public function getNextTransition(): ?string;
}
