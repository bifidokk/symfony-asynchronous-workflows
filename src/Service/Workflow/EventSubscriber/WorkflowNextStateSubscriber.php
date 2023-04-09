<?php
declare(strict_types=1);

namespace App\Service\Workflow\EventSubscriber;

use App\Service\Workflow\Event\WorkflowNextStateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Registry;

class WorkflowNextStateSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly Registry $workflowRegistry
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WorkflowNextStateEvent::class => 'applyNextState',
        ];
    }

    public function applyNextState(WorkflowNextStateEvent $event): void
    {
        $workflowEntry = $event->getWorkflowEntry();

        if (!$this->workflowRegistry->has(
            $workflowEntry,
            $workflowEntry->getWorkflowType()->value)
        ) {
            throw new \RuntimeException(
                sprintf(
                    'There is no workflow with type %s',
                    $workflowEntry->getWorkflowType()->value
                )
            );
        }

        $workflow = $this->workflowRegistry->get(
            $workflowEntry,
            $workflowEntry->getWorkflowType()->value
        );

        $workflow->apply(
            $workflowEntry,
            $workflowEntry->getNextTransition()
        );
    }
}
