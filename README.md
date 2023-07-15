## Asynchronous Symfony workflows

This project demonstrates how Symfony workflow (state machine) might be used to split complex business logic into small pieces and ensure system reliability and fault tolerance.

Any business logic flow is divided into state-machine transitions. Every transition is executed transactionally.
If transition execution failed we can retry it later by command or asynchronously.
Every transition contains only business logic and doesn't depend on the workflow implementation.
It uses only two entities: `WorkflowEnvelope` and `WorkflowStampInterface` to use data from previous transitions and pass its own result to the next steps.

As example, an "Order" workflow is created:

```php
src/Service/Order/OrderService.php
```

Let's imagine a flow: we create an order, send it to email and mark it as "sent".
There are many points of failure: invalid order data during creation, vendor email provider failure, etc
The example demonstrates how it's possible to retry the flow after failure.
