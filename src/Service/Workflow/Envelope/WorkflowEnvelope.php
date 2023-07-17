<?php
declare(strict_types=1);

namespace App\Service\Workflow\Envelope;


use App\Service\Workflow\WorkflowStampInterface;

class WorkflowEnvelope
{
    private array $stamps;

    /**
     * @param WorkflowStampInterface[] $stamps
     */
    public function __construct(array $stamps = [])
    {
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

    public function getStamp(string $stampClass): WorkflowStampInterface
    {
        $stamps = $this->stamps[$stampClass] ?? [];

        if (count($stamps) === 0) {
            throw new \RuntimeException(sprintf('Stamp with type %s is not found', $stampClass));
        }

        return reset($stamps);
    }

    public function hasStamp(string $stampClass): bool
    {
        return isset($this->stamps[$stampClass]);
    }
}
