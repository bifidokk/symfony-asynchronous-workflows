## Asynchronous Symfony workflows

This project demonstrates how Symfony workflow (state machine) might be used to split complex business logic into small pieces and ensure system reliability and fault tolerance.

Any business logic flow is divided into state-machine transitions. Every transition is executed transactionally.
If transition execution failed we can retry it later by command or asynchronously.
Every transition contains only business logic and doesn't depend on the workflow implementation.
It uses only two entities: `WorkflowEnvelope` and `WorkflowStampInterface` to use data from previous transitions and pass its own result to the next steps.

As an example, an "Order" workflow is created. The example demonstrates how it's possible to retry the flow after failure:

```php
src/Service/Order/OrderService.php
```

Let's imagine a flow: we create an order, send it to email and mark it as "sent".
There are many points of failure: invalid order data during creation, vendor email provider failure, etc

## Symfony Workflow

At first, need to create a new [Symfony workflow](https://symfony.com/doc/current/workflow.html):

```yaml
framework:
    workflows:
        order_send:
            type: state_machine
            supports:
                - App\Entity\WorkflowEntry
            marking_store:
                type: 'method'
                property: 'currentState'
            places:
                - initialised
                - verified
                - sent
                - marked_as_sent
            transitions:
                verify_order:
                    from: initialised
                    to: verified
                send_order:
                    from: verified
                    to: sent
                mark_order_as_sent:
                    from: sent
                    to: marked_as_sent
```

## Core functionality

Every workflow handles ```App\Entity\WorkflowEntry``` that keeps current and next states and store business logic data using stamps ```App\Service\Workflow\WorkflowStampInterface```.
Stamps are serialized in the Envelope ```App\Service\Workflow\Envelope\WorkflowEnvelope```.

The heart of every workflow is ```App\Service\Workflow\WorkflowHandler``` and ```App\Service\Workflow\EventSubscriber```.

The first one starts the workflow execution and handles all exceptions.
The second one runs transitions and saves transition's result in the database.

There several exceptions that allows to control the workflow:

```App\Service\Workflow\Exception\StopWorkflowException``` - finally stops the workflow is some permanent error occurred

```App\Service\Workflow\Exception\WorkflowInternalErrorException``` - temporary stops the workflow to retry it later, for example, if 3rd party service is temporarily unavailable

```App\Service\Workflow\Exception\ProceedWorkflowInQueueException``` - temporary stops the workflow and send the retry process to a queue


## Transitions


To implement this flow let's add three transitions. Every transition must implement
```WorkflowTransitionInterface```.

```WorkflowTransitionInterface::handle``` method should contain transition implementation.

```WorkflowTransitionInterface::getNextTransition``` returns next transition value or null if current transition is the last one.

```WorkflowTransitionInterface::getState``` returns the flow state which should be set to the workflow entry after the transition is done

Check the ```src/Service/Workflow/Order/Transition``` directory for details.


**VerifyOrder**

```php
class VerifyOrder implements WorkflowTransitionInterface
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
    ) {
    }

    public function handle(WorkflowEnvelope $envelope): WorkflowEnvelope
    {
        /** @var OrderIdStamp $orderIdStamp */
        $orderIdStamp = $envelope->getStamp(OrderIdStamp::class);
        $orderId = $orderIdStamp->getOrderId();

        $order = $this->orderRepository->find($orderId);
        ....
        return $envelope;
    }

    public function getNextTransition(): ?string
    {
        return Transition::SendOrder->value;
    }

    public function getState(): ?string
    {
        return State::Verified->value;
    }
}
```

**SendOrder**

```php
class SendOrder implements WorkflowTransitionInterface
{
    public function handle(WorkflowEnvelope $envelope): WorkflowEnvelope
    {
        ....
        return $envelope;
    }

    public function getNextTransition(): ?string
    {
        return Transition::MarkOrderAsSent->value;
    }

    public function getState(): ?string
    {
        return State::Sent->value;
    }
}
```

**MarkOrderAsSent**

```php
class MarkOrderAsSent implements WorkflowTransitionInterface
{
    public function handle(WorkflowEnvelope $envelope): WorkflowEnvelope
    {
        ....
        return $envelope;
    }

    public function getNextTransition(): ?string
    {
        return null;
    }

    public function getState(): ?string
    {
        return State::MarkedAsSent->value;
    }
}
```

After that transition should be added to registry in the ```services.yaml```

```yaml
app.transitions:
    class: Symfony\Component\DependencyInjection\ServiceLocator
    tags: [ 'container.service_locator' ]
    arguments:
        -
            order_send.verify_order: '@App\Service\Workflow\Order\Transition\VerifyOrder'
            order_send.send_order: '@App\Service\Workflow\Order\Transition\SendOrder'
            order_send.mark_order_as_sent: '@App\Service\Workflow\Order\Transition\MarkOrderAsSent'
```
