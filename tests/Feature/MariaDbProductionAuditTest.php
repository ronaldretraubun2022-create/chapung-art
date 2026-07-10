<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

function auditForeignKeys(string $table): array
{
    return DB::select("PRAGMA foreign_key_list('{$table}')");
}

function auditIndexNames(string $table): array
{
    return collect(DB::select("PRAGMA index_list('{$table}')"))
        ->pluck('name')
        ->filter()
        ->values()
        ->all();
}

function auditTableHasUniqueColumn(string $table, string $column): bool
{
    return collect(DB::select("PRAGMA index_list('{$table}')"))
        ->filter(fn (object $index): bool => (bool) $index->unique)
        ->contains(function (object $index) use ($column): bool {
            return collect(DB::select("PRAGMA index_info('{$index->name}')"))
                ->pluck('name')
                ->contains($column);
        });
}

function auditHasForeignKeyAction(string $table, string $column, string $referencedTable, string $deleteAction): bool
{
    return collect(auditForeignKeys($table))->contains(function (object $foreignKey) use ($column, $referencedTable, $deleteAction): bool {
        return $foreignKey->{'from'} === $column
            && $foreignKey->table === $referencedTable
            && strtoupper((string) $foreignKey->on_delete) === strtoupper($deleteAction);
    });
}

test('mariadb production foreign keys use intended cascade and null actions', function () {
    expect(auditHasForeignKeyAction('artworks', 'category_id', 'categories', 'SET NULL'))->toBeTrue()
        ->and(auditHasForeignKeyAction('artworks', 'artist_id', 'artists', 'SET NULL'))->toBeTrue()
        ->and(auditHasForeignKeyAction('artworks', 'collection_id', 'collections', 'SET NULL'))->toBeTrue()
        ->and(auditHasForeignKeyAction('posts', 'author_id', 'users', 'SET NULL'))->toBeTrue()
        ->and(auditHasForeignKeyAction('orders', 'customer_id', 'customers', 'SET NULL'))->toBeTrue()
        ->and(auditHasForeignKeyAction('order_items', 'order_id', 'orders', 'CASCADE'))->toBeTrue()
        ->and(auditHasForeignKeyAction('payments', 'order_id', 'orders', 'CASCADE'))->toBeTrue()
        ->and(auditHasForeignKeyAction('shipments', 'order_id', 'orders', 'CASCADE'))->toBeTrue()
        ->and(auditHasForeignKeyAction('certificates', 'artwork_id', 'artworks', 'CASCADE'))->toBeTrue()
        ->and(auditHasForeignKeyAction('certificates', 'artist_id', 'artists', 'SET NULL'))->toBeTrue()
        ->and(auditHasForeignKeyAction('artwork_tag', 'artwork_id', 'artworks', 'CASCADE'))->toBeTrue()
        ->and(auditHasForeignKeyAction('artwork_tag', 'tag_id', 'tags', 'CASCADE'))->toBeTrue();
});

test('mariadb production indexes exist for gallery reports and published content', function () {
    expect(auditIndexNames('artworks'))->toContain(
        'artworks_featured_created_idx',
        'artworks_category_created_idx',
        'artworks_artist_created_idx',
        'artworks_collection_created_idx',
        'artworks_status_created_idx',
        'artworks_price_idx',
    )->and(auditIndexNames('photographies'))->toContain(
        'photos_featured_created_idx',
        'photos_category_created_idx',
        'photos_artist_created_idx',
        'photos_collection_created_idx',
        'photos_status_created_idx',
        'photos_price_idx',
    )->and(auditIndexNames('posts'))->toContain(
        'posts_status_published_idx',
        'posts_category_status_published_idx',
        'posts_featured_created_idx',
    )->and(auditIndexNames('page_views'))->toContain(
        'page_views_type_viewed_idx',
        'page_views_device_viewed_idx',
    );
});

test('mariadb production unique constraints protect public identifiers', function () {
    expect(auditTableHasUniqueColumn('users', 'email'))->toBeTrue()
        ->and(auditTableHasUniqueColumn('artworks', 'slug'))->toBeTrue()
        ->and(auditTableHasUniqueColumn('photographies', 'slug'))->toBeTrue()
        ->and(auditTableHasUniqueColumn('artists', 'slug'))->toBeTrue()
        ->and(auditTableHasUniqueColumn('collections', 'slug'))->toBeTrue()
        ->and(auditTableHasUniqueColumn('tags', 'slug'))->toBeTrue()
        ->and(auditTableHasUniqueColumn('orders', 'order_number'))->toBeTrue()
        ->and(auditTableHasUniqueColumn('orders', 'invoice_number'))->toBeTrue()
        ->and(auditTableHasUniqueColumn('certificates', 'certificate_number'))->toBeTrue();
});

test('mariadb production json columns and audit documentation exist', function () {
    expect(Schema::hasColumn('homepage_sections', 'payload'))->toBeTrue()
        ->and(Schema::hasColumn('seo_metas', 'schema_json'))->toBeTrue()
        ->and(Schema::hasColumn('activity_logs', 'properties'))->toBeTrue()
        ->and(File::exists(base_path('docs/MARIADB_PRODUCTION_AUDIT.md')))->toBeTrue();
});

test('mariadb enum compatibility migration covers mysql and mariadb drivers', function () {
    $migration = File::get(database_path('migrations/2026_07_11_370000_ensure_posts_status_review_enum_for_mariadb.php'));

    expect($migration)->toContain("'mysql', 'mariadb'")
        ->and($migration)->toContain("ENUM('draft', 'review', 'published', 'archived')")
        ->and($migration)->toContain("ENUM('draft', 'published', 'archived')");
});
