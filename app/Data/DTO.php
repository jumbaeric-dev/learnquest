<?php

namespace App\Data;

use JsonSerializable;

abstract readonly class DTO implements JsonSerializable
{
    /**
     * Convert the DTO into an array recursively.
     */
    public function toArray(): array
    {
        $data = [];

        foreach (get_object_vars($this) as $key => $value) {
            $data[$key] = $this->transform($value);
        }

        return $data;
    }

    /**
     * Transform nested values.
     */
    protected function transform(mixed $value): mixed
    {
        if ($value instanceof self) {
            return $value->toArray();
        }

        if (is_array($value)) {
            return array_map(
                fn ($item) => $this->transform($item),
                $value
            );
        }

        return $value;
    }

    /**
     * JSON serialization support.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}