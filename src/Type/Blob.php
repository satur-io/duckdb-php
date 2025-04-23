<?php

declare(strict_types=1);

namespace Saturio\DuckDB\Type;

class Blob
{
    public function __construct(
        private readonly string $rawData,
    ) {
    }

    public function data(): string
    {
        return $this->rawData;
    }

    /**
     * Convert non-printable characters to HEX.
     */
    public function __toString(): string
    {
        return preg_replace_callback('/[^\x20-\x7E]/', fn ($match) => sprintf('\x%02X', ord($match[0])), $this->rawData);
    }
}
