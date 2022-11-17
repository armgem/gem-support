<?php

namespace GemSupport;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class GemDB
{
    /**
     * checks if the given table exists - and caches the result
     *
     * @param string $table
     * @return bool
     */
    public function hasTable(string $table): bool
    {
        return $this->cache('gem_'. $table . '_table_exists', function () use ($table) {
            return Schema::hasTable($table);
        });
    }

    /**
     * check if the given table has the given column - and caches the query
     *
     * @param string $table
     * @param string $column
     * @return bool
     */
    public function hasColumn(string $table, string $column): bool
    {
        $column = str_replace($table . '.', '', $column);
        return $this->cache('gem_' . $table . '_table_has_column_' . $column, function () use ($table, $column) {
            return Schema::hasColumn($table, $column);
        });
    }

    /**
     * qualify columns with table and based prefix give alias with prefix
     *
     * @param string $table
     * @param array $columns
     * @param string|bool $prefix
     * @return array|string
     */
    public function qualifyColumns(string $table, array $columns, string|bool $prefix = ''): array|string
    {
        $result = [];
        foreach ($columns as $column) {
            $result[] = $this->qualifyColumn($table, $column, $prefix);
        }

        return $result;
    }

    /**
     * qualify column with table and based prefix give alias with prefix
     *
     * @param string $table
     * @param string $column
     * @param string|bool $prefix
     * @return string
     */
    public function qualifyColumn(string $table, string $column, string|bool $prefix = ''): string
    {
        $column = Str::start($column, $table . '.');

        if (!empty($prefix) || is_bool($prefix)) {
            $columnName = Str::afterLast($column, '.');
            $column .= is_bool($prefix) ? ' as ' . $columnName : ' as ' . ($prefix . '_' . $columnName);
        }

        return $column;
    }

    /**
     * get all tables
     *
     * @return array
     */
    public function getAllTables(): array
    {
        return $this->cache('gem_db_all_tables', function () {
            $tablesInDb = DB::select('SHOW TABLES');
            $key = "Tables_in_" . config('database.connections.mysql.database');
            $tables = [];

            foreach ($tablesInDb as $table) {
                $tables[] = $table->{$key};
            }

            return $tables;
        });
    }

    /**
     * Get single table columns information
     *
     * @param string $table
     * @return Collection
     */
    public function getTableColumnsInfo(string $table): Collection
    {
        return $this->cache('gem_' . $table . '_table_columns_info', function () use ($table) {
            $query = sprintf($this->getColumnsInfoQuery() . ' and table_name="%s"', $table);
            $columnsInfo = DB::select($query);
            $columns = [];
            foreach ($columnsInfo as $columnDetails) {
                $columns[$columnDetails->COLUMN_NAME] = $this->getColumnInfo($columnDetails);
            }
            return collect($columns);
        });
    }

    /**
     * Get all tables and columns information
     *
     * @return Collection
     */
    public function getDBStructure(): Collection
    {
        return $this->cache('gem_db_all_table_columns_info', function () {
            $query = $this->getColumnsInfoQuery();

            $columnsInfo = DB::select($query);
            $tables = [];
            foreach ($columnsInfo as $columnDetails) {
                $tables[$columnDetails->TABLE_NAME][$columnDetails->COLUMN_NAME] = $this->getColumnInfo($columnDetails);
            }
            return collect($tables);
        });

    }

    /**
     * @param object $columnDetails
     * @return array
     */
    protected function getColumnInfo(object $columnDetails): array
    {
        // TODO maybe use later CHARACTER_OCTET_LENGTH, NUMERIC_PRCEISION, NUMERIC_SCALE, DATETIME_PRECISION, CHARACTER_SET_NAME, COLLATION_NAME
        return [
            'position' => $columnDetails->ORDINAL_POSITION,
            'data_type' => $columnDetails->DATA_TYPE,
            'key' => $columnDetails->COLUMN_KEY, // PRI, UNI, MUL, ''
            'is_nullable' => 'YES' == $columnDetails->IS_NULLABLE,
            'default' => $columnDetails->COLUMN_DEFAULT,
            'extra' => $columnDetails->EXTRA,
            'column_type' => $columnDetails->COLUMN_TYPE,
            'comment' => $columnDetails->COLUMN_COMMENT,
            'unsigned' => Str::contains($columnDetails->COLUMN_TYPE, 'unsigned'),
            'length' => $this->detectLength($columnDetails)
        ];
    }

    /**
     * @param object $columnDetails
     * @return int|null
     */
    protected function detectLength(object $columnDetails): int|null
    {
        if (in_array($columnDetails->DATA_TYPE, ['enum', 'set'])) {
            return null;
        }

        $hasLengthInfo = Str::containsAll($columnDetails->COLUMN_TYPE, ['(', ')']);
        if (!$hasLengthInfo && !$columnDetails->CHARACTER_MAXIMUM_LENGTH) {
            return null;
        }

        $detectedLength = $hasLengthInfo ? Str::between($columnDetails->COLUMN_TYPE, '(', ')') : null;

        if ($columnDetails->CHARACTER_MAXIMUM_LENGTH == $detectedLength) {
            return $detectedLength;
        }

        if (!empty($detectedLength) && !is_numeric($detectedLength)) {
            return null;
        }

        return $columnDetails->CHARACTER_MAXIMUM_LENGTH ?: $detectedLength;
    }

    /**
     * @return string
     */
    protected function getColumnsInfoQuery(): string
    {
        return sprintf("SELECT * FROM information_schema.columns WHERE table_schema ='%s'", config('database.connections.mysql.database'));
    }

    /**
     * @param string $cacheKey
     * @param \Closure $callback
     * @return mixed
     */
    protected function cache(string $cacheKey, \Closure $callback): mixed
    {
        $days = Config::get('gem_support.db.' . App::environment() . '.cache.days', false);

        return $days === false ? $callback() : Cache::remember($cacheKey, $days * 24 * 60 * 60, $callback);
    }
}
