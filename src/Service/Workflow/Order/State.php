<?php
declare(strict_types=1);

namespace App\Service\Workflow\Order;

enum State: string
{
    case Verified = 'verified';

    case Approved = 'approved';

    case SentToEmail = 'sent_to_email';

    case MarkedAsSent = 'marked_as_sent';
}
