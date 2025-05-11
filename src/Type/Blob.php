<?php

declare(strict_types=1);

namespace Saturio\DuckDB\Type;

use JsonSerializable;

class Blob implements JsonSerializable
{
    public function __construct(
        private readonly string $rawData,
    ) {
    }

    public function data(): string
    {
        return $this->rawData;
    }

    /** @return array<int> */
    public function asIntArray(): array
    {
        $array = [];
        for ($i = 0; $i < strlen($this->rawData); ++$i) {
            $array[] = ord($this->rawData[$i]);
        }

        return $array;
    }

    /**
     * Convert non-printable characters to HEX.
     */
    public function __toString(): string
    {
        return preg_replace_callback('/[^\x20-\x7E]/', fn ($match) => sprintf('\x%02X', ord($match[0])), $this->rawData);
    }

    /**
     * Convert HEX-encoded non-printable characters (e.g., \x0A) back to raw bytes.
     */
    public static function fromHexEncodedString(string $encodedString): Blob
    {
        return new Blob(preg_replace_callback(
            '/\\\x([0-9A-Fa-f]{2})/',
            function ($match) {
                return chr(hexdec($match[1]));
            },
            $encodedString
        ));
    }

    public function jsonSerialize(): string
    {
        return $this->__toString();
    }
}
