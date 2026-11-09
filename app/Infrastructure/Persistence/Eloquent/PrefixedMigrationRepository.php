<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Migrations\DatabaseMigrationRepository;
use RuntimeException;

final class PrefixedMigrationRepository extends DatabaseMigrationRepository
{
    public function repositoryExists()
    {
        return $this->getConnection()->getSchemaBuilder()->hasTable($this->repositoryTable());
    }

    protected function table()
    {
        return $this->getConnection()->table($this->repositoryTable())->useWritePdo();
    }

    public function deleteRepository()
    {
        $this->getConnection()->getSchemaBuilder()->drop($this->repositoryTable());
    }

    private function repositoryTable(): string
    {
        $schema = $this->getConnection()->getSchemaBuilder();
        $prefixed = $schema->hasTable($this->table);
        $legacy = $schema->hasTable('migrations');
        if ($prefixed && $legacy) {
            throw new RuntimeException('Both migrations and tbl_migrations exist. Resolve the duplicate migration ledgers before migrating.');
        }
        // Resolve per operation so logging and rollback continue after the ledger is renamed.
        return $legacy ? 'migrations' : $this->table;
    }
}
