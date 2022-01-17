<?php

declare(strict_types=1);

namespace Bolt\Event;

use Symfony\Contracts\EventDispatcher\Event;

class PostEvent extends Event
{
    public const POST_DATA = 'bolt.post_data';

    /** @var array  */
    private $formData;

    public function __construct(array $formData)
    {
        $this->formData = $formData;
    }

    public function getFormData(): array
    {
        return $this->formData;
    }
}
