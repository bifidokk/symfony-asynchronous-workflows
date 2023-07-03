<?php
declare(strict_types=1);

namespace App\Service\Workflow\EventSubscriber;

use App\Entity\WorkflowEntry;
use App\Service\Workflow\Event\WorkflowNextStateEvent;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

class WorkflowTransitionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ServiceLocator $transitions,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.transition' => 'handleTransition',
        ];
    }

    public function handleTransition(Event $event): void
    {
        /** @var WorkflowEntry $workflowEntry */
        $workflowEntry = $event->getSubject();
        $this->entityManager->getConnection()->beginTransaction();

        try {
            $transition = $this->transitions->get($workflowEntry->getNextTransition());
            $transition->handle($workflowEntry);

            $this->entityManager->getConnection()->commit();
        } catch (\Throwable $exception) {
            $this->entityManager->getConnection()->rollBack();

            throw $exception;
        }

        if ($workflowEntry->getNextTransition() !== null) {
            $this->eventDispatcher->dispatch(new WorkflowNextStateEvent($workflowEntry));
        }
    }
}
