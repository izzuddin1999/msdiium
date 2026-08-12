<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

trait UsesHrInternSchema
{
    public function getTable(): string
    {
        $table = $this->table ?? parent::getTable();

        if (DB::connection($this->getConnectionName())->getDriverName() === 'sqlite') {
            return Str::afterLast($table, '.');
        }

        return $table;
    }
}
