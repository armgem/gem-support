<?php

namespace GemSupport\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static bool hasColumn(string $table, string $column)
 * @method static string qualifyColumn(string $table, string $column, string|bool $prefix = '')
 * @method static array qualifyColumns(string $table, array $columns, string|bool $prefix = '')
 * @method static bool hasTable(string $table)
 * @method static array getAllTables()
 * @method static Collection getTableColumnsInfo(string $table)
 * @method static Collection getDBStructure()
 */
class GemDB extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'gem-db';
    }
}
