<?php
declare(strict_types=1);

namespace App\Service\Workflow\Exception;

class CorruptedEnvelopeException extends \RuntimeException
{
    public static function shouldHaveExactOneStamp(string $stampClass, int $count): \RuntimeException
    {
        return new self(
            sprintf(
                'Envelope should have exact one "%s" stamp, but %s are found',
                $stampClass,
                $count
            )
        );
    }
}
