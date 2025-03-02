<?php

declare(strict_types=1);

namespace Saturio\DuckDB\FFI;

use Saturio\DuckDB\Native\FFI\CData as NativeCData;

class CData implements \ArrayAccess, CDataInterface
{
    public function __construct(
        public NativeCData $cdata,
    ) {
    }

    public static function from(NativeCData $cdata): self
    {
        return new self($cdata);
    }

    public function __set(string $name, string|float|int|bool|NativeCData|null $value): void
    {
        $this->cdata->$name = $value;
    }

    public function __get(string $name): string|float|int|bool|NativeCData|null
    {
        return $this->cdata->$name;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->cdata[$offset]);
    }

    public function offsetGet(mixed $offset): string|float|int|bool|NativeCData|null
    {
        return $this->cdata[$offset];
    }

    public function get(int $offset): string|float|int|bool|NativeCData|null
    {
        return $this->cdata[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->cdata[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->cdata[$offset]);
    }

    public function getInternalCData(): string|float|int|bool|NativeCData|null
    {
        return $this->cdata;
    }
}
