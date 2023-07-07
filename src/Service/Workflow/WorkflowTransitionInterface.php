<?php
declare(strict_types=1);

namespace App\Service\Workflow;

use App\Entity\WorkflowEntry;
use App\Service\Workflow\Envelope\WorkflowEnvelope;

interface WorkflowTransitionInterface
{
    public function handle(WorkflowEnvelope $envelope): WorkflowEnvelope;

    public function getNextTransition(): ?string;

    public function getState(): ?string;
}
