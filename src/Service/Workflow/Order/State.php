<?php
declare(strict_types=1);

namespace App\Service\Workflow\Order;

enum State: string
{
    case Verified = 'verified';

    case Sent = 'sent';

    case MarkedAsSent = 'marked_as_sent';
}
