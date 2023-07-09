<?php
declare(strict_types=1);

namespace App\Service\Workflow;

enum WorkflowStatus: string
{
    case Started = 'started';

    case Finished = 'finished';

    case Stopped = 'stopped';

    case QueueProcessing = 'queue_processing';
}
