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
        $this->stamps[$stamp::class][] = $stamp;
    }

    public function getStamps(): array
    {
        return $this->stamps;
    }

    public function getStamp(string $stampFqcn): WorkflowStampInterface
    {
        $stamps = $this->stamps[$stampFqcn] ?? [];

        if (count($stamps) === 0) {
            throw new \RuntimeException(sprintf('Stamp with type %s is not found', $stampFqcn));
        }

        return reset($stamps);
    }

    public function hasStamp(string $stampFqcn): bool
    {
        return isset($this->stamps[$stampFqcn]);
    }
}
