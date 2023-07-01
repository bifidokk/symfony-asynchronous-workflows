<?php
declare(strict_types=1);

namespace App\Service\Workflow\EventSubscriber;

use App\Service\Workflow\Event\WorkflowNextStateEvent;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class WorkflowNextStateSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ServiceLocator $workflows,
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

        if (!$this->workflows->has(
            $workflowEntry->getWorkflowType()->value)
        ) {
            throw new \RuntimeException(
                sprintf(
                    'There is no workflow with type %s',
                    $workflowEntry->getWorkflowType()->value
                )
            );
        }

        $workflow = $this->workflows->get(
            $workflowEntry->getWorkflowType()->value
        );

        $workflow->apply(
            $workflowEntry,
            $workflowEntry->getNextTransition()
        );
    }
}
