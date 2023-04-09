<?php

namespace App\Service\Workflow;

enum WorkflowType: string
{
    case DefaultType = 'default';

    case OrderComplete = 'order_complete';
}
