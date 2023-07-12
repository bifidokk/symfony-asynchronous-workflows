<?php
declare(strict_types=1);

namespace App\Service\Workflow\Exception;

class WorkflowProcessInQueueException extends \RuntimeException
{
    /**
     * @var string
     */
    protected $message = 'An internal error occurred. Workflow will be processed in queue';
}
