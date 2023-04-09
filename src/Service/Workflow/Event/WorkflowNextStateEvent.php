<?php
declare(strict_types=1);

namespace App\Service\Workflow\Event;

use App\Entity\WorkflowEntry;

class WorkflowNextStateEvent
{
    private WorkflowEntry $workflowEntry;

    public function __construct(WorkflowEntry $workflowEntry)
    {
        $this->workflowEntry = $workflowEntry;
    }

    public function getWorkflowEntry(): WorkflowEntry
    {
        return $this->workflowEntry;
    }
}
