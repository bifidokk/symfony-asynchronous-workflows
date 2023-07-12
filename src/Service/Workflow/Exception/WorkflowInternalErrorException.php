<?php
declare(strict_types=1);

namespace App\Service\Workflow\Exception;

class WorkflowInternalErrorException extends \RuntimeException
{
    /**
     * @var string
     */
    protected $message = 'An internal error occurred';
}
