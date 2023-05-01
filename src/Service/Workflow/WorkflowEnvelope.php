<?php
declare(strict_types=1);

namespace App\Service\Workflow;

class WorkflowEnvelope
{
    private array $stamps;

    /**
     * @param WorkflowStampInterface[] $stamps
     */
    public function __construct(array $stamps = [])
    {
        $this->replace($stamps);
    }

    /**
     * @param WorkflowStampInterface[] $stamps
     */
    public function replace(array $stamps): void
    {
        $this->stamps = [];

        foreach ($stamps as $stamp) {
            $this->addStamp($stamp);
        }
    }

    public function addStamp(WorkflowStampInterface $stamp): void
    {
        $this->stamps[\get_class($stamp)][] = $stamp;
    }

    public function getStamps(): array
    {
        return $this->stamps;
    }
}
