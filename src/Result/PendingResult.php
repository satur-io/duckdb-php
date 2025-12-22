<?php

declare(strict_types=1);

namespace Saturio\DuckDB\Result;

use Saturio\DuckDB\Exception\ExecutePendingException;
use Saturio\DuckDB\FFI\DuckDB;
use Saturio\DuckDB\Native\FFI\CData as NativeCData;

class PendingResult
{
    public const int DUCKDB_PENDING_RESULT_READY = 0;
    public const int DUCKDB_PENDING_RESULT_NOT_READY = 1;
    public const int DUCKDB_PENDING_ERROR = 2;
    public const int DUCKDB_PENDING_NO_TASKS_AVAILABLE = 3;

    public function __construct(
        private readonly DuckDB $ffi,
        public readonly NativeCData $pendingResult,
    ) {
    }

    public function executeTask(): int
    {
        return $this->ffi->executeTask($this->pendingResult);
    }

    public function error(): ?string
    {
        return $this->ffi->pendingError($this->pendingResult);
    }

    /**
     * @throws ExecutePendingException
     */
    public function execute(): ResultSet
    {
        $result = $this->ffi->new('duckdb_result');
        if ($this->ffi->executePending($this->pendingResult, $this->ffi->addr($result)) === $this->ffi->error()) {
            throw new ExecutePendingException('Error executing pending: '.$this->ffi->resultError($this->ffi->addr($result)));
        }

        return new ResultSet($this->ffi, $result);
    }

    public function __destruct()
    {
        $this->ffi->destroyPending($this->ffi->addr($this->pendingResult));
    }
}
