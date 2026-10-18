<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Safety guard of the local demo-data command (portfolio-demo:install).
 *
 * The test suite runs on in-memory SQLite (phpunit.xml), so the command must
 * refuse to run here and must not touch the database.
 */
class PortfolioDemoInstallTest extends TestCase
{
    public function test_refuses_to_run_against_any_database_other_than_the_portfolio_database(): void
    {
        $this->assertNotSame('huffaztrack_portfolio', DB::connection()->getDatabaseName());

        DB::enableQueryLog();

        $this->artisan('portfolio-demo:install')
            ->expectsOutputToContain("Refusing to run: the active database is not 'huffaztrack_portfolio'. Nothing was written.")
            ->assertFailed();

        $this->assertSame([], DB::getQueryLog(), 'The command ran queries before its database guard.');
    }

    public function test_seed_activity_refuses_to_run_against_any_database_other_than_the_portfolio_database(): void
    {
        DB::enableQueryLog();

        $this->artisan('portfolio-demo:seed-activity')
            ->expectsOutputToContain("Refusing to run: the active database is not 'huffaztrack_portfolio'. Nothing was written.")
            ->assertFailed();

        $this->assertSame([], DB::getQueryLog(), 'The command ran queries before its database guard.');
    }
}
