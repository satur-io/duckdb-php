<?php

declare(strict_types=1);

namespace Saturio\DuckDB\Type;

enum TimePrecision: string
{
    case SECONDS = 'seconds';
    case MILLISECONDS = 'milliseconds';
    case MICROSECONDS = 'microseconds';
    case NANOSECONDS = 'nanoseconds';
}
