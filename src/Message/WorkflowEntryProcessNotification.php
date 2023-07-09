<?php
declare(strict_types=1);

namespace App\Message;

use Symfony\Component\Uid\Uuid;

class WorkflowEntryProcessNotification
{
    private Uuid $workflowId;

    public function __construct(Uuid $workflowId)
    {
        $this->workflowId = $workflowId;
    }

    public function getWorkflowId(): Uuid
    {
        return $this->workflowId;
    }
}
