<?php

namespace App\Models;

use App\Enums\ExtraPointCategory;

class ExtraPoint
{
    /**
     * @var ExtraPointCategory
     */
    private ExtraPointCategory $category;

    /**
     * @var array
     */
    private array $payload;

    /**
     * @param ExtraPointCategory $_category
     * @param array $_payload
     */
    public function __construct(
        ExtraPointCategory $_category,
        array $_payload,
    )
    {
        $this->category = $_category;
        $this->payload = $_payload;
    }

    /**
     * @return ExtraPointCategory|null
     */
    public function getCategory(): ?ExtraPointCategory
    {
        return $this->category;
    }

    /**
     * @return array
     */
    public function getPayload(): array
    {
        return $this->payload;
    }
}