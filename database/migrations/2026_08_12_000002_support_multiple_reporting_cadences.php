<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $objectiveColumn = DB::selectOne("SHOW COLUMNS FROM goal_objectives WHERE Field = 'reporting_frequency'");

            if ($objectiveColumn && str_starts_with((string) $objectiveColumn->Type, 'enum')) {
                DB::statement('ALTER TABLE goal_objectives ADD reporting_frequency_new JSON NULL AFTER planned_weeks');
                DB::statement("UPDATE goal_objectives SET reporting_frequency_new = JSON_ARRAY(COALESCE(reporting_frequency, 'weekly'))");
                DB::statement('ALTER TABLE goal_objectives DROP COLUMN reporting_frequency');
                DB::statement('ALTER TABLE goal_objectives CHANGE reporting_frequency_new reporting_frequency JSON NOT NULL');
            }

            if (! Schema::hasColumn('weekly_updates', 'reporting_frequency')) {
                Schema::table('weekly_updates', function (Blueprint $table) {
                    $table->enum('reporting_frequency', ['daily', 'weekly', 'monthly'])
                        ->default('weekly')
                        ->after('report_date');
                });
            }

            $this->replaceWeeklyUpdatePeriodIndex();

            return;
        }

        if (! Schema::hasColumn('weekly_updates', 'reporting_frequency')) {
            Schema::table('weekly_updates', function (Blueprint $table) {
                $table->string('reporting_frequency')->default('weekly')->after('report_date');
            });
        }
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            try {
                Schema::table('weekly_updates', function (Blueprint $table) {
                    $table->dropUnique('weekly_updates_period_unique');
                });
            } catch (Throwable) {
                //
            }

            if (Schema::hasColumn('weekly_updates', 'reporting_frequency')) {
                Schema::table('weekly_updates', function (Blueprint $table) {
                    $table->dropColumn('reporting_frequency');
                });
            }

            $objectiveColumn = DB::selectOne("SHOW COLUMNS FROM goal_objectives WHERE Field = 'reporting_frequency'");

            if ($objectiveColumn && ! str_starts_with((string) $objectiveColumn->Type, 'enum')) {
                DB::statement("ALTER TABLE goal_objectives ADD reporting_frequency_old ENUM('daily', 'weekly', 'monthly') NOT NULL DEFAULT 'weekly' AFTER planned_weeks");
                DB::statement("UPDATE goal_objectives SET reporting_frequency_old = COALESCE(JSON_UNQUOTE(JSON_EXTRACT(reporting_frequency, '$[0]')), 'weekly')");
                DB::statement('ALTER TABLE goal_objectives DROP COLUMN reporting_frequency');
                DB::statement('ALTER TABLE goal_objectives CHANGE reporting_frequency_old reporting_frequency ENUM(\'daily\', \'weekly\', \'monthly\') NOT NULL DEFAULT \'weekly\'');
            }

            Schema::table('weekly_updates', function (Blueprint $table) {
                $table->unique(
                    ['goal_objective_id', 'user_id', 'report_period_start'],
                    'weekly_updates_period_unique'
                );
            });
        }
    }

    private function replaceWeeklyUpdatePeriodIndex(): void
    {
        $indexRows = collect(DB::select("SHOW INDEX FROM weekly_updates WHERE Key_name = 'weekly_updates_period_unique'"))
            ->sortBy('Seq_in_index')
            ->values();
        $columns = $indexRows->pluck('Column_name')->all();
        $desired = ['goal_objective_id', 'user_id', 'reporting_frequency', 'report_period_start'];

        if ($columns === $desired) {
            return;
        }

        if ($indexRows->isNotEmpty()) {
            $hasObjectiveIndex = collect(DB::select('SHOW INDEX FROM weekly_updates'))
                ->contains(fn ($index) => $index->Key_name === 'weekly_updates_goal_objective_id_index');

            if (! $hasObjectiveIndex) {
                DB::statement('ALTER TABLE weekly_updates ADD INDEX weekly_updates_goal_objective_id_index (goal_objective_id)');
            }

            DB::statement('ALTER TABLE weekly_updates DROP INDEX weekly_updates_period_unique');
        }

        DB::statement('ALTER TABLE weekly_updates ADD UNIQUE weekly_updates_period_unique (goal_objective_id, user_id, reporting_frequency, report_period_start)');
    }
};
