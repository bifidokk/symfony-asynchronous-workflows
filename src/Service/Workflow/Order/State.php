<?php
declare(strict_types=1);

namespace App\Service\Workflow\Order;

enum State: string
{
    case Verified = 'verified';

    case Confirmed = 'confirmed';

    case Completed = 'completed';
}
