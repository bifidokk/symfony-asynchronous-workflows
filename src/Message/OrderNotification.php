<?php
declare(strict_types=1);

namespace App\Message;

use Symfony\Component\Uid\Uuid;

class OrderNotification
{
    private Uuid $id;
    private string $content;

    public function __construct(
        Uuid $id,
        string $content
    ) {
        $this->id = $id;
        $this->content = $content;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
