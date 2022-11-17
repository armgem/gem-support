<?php

namespace GemSupport\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

abstract class RenameMigration extends Migration
{
    /**
     * @var array
     */
    protected array $renameColumns = [];

    /**
     * @var array
     */
    protected array $renameTable = [];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach ($this->renameColumns as $tableName => $columnConfig) {
            Schema::table($tableName, function (Blueprint $table) use ($columnConfig) {
                foreach ($columnConfig as $old => $new) {
                    $table->renameColumn($old, $new);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach ($this->renameColumns as $tableName => $columnConfig) {
            Schema::table($tableName, function (Blueprint $table) use ($columnConfig) {
                foreach ($columnConfig as $old => $new) {
                    $table->renameColumn($new, $old);
                }
            });
        }
    }

}
