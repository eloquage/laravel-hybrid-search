# Laravel Hybrid Search

![Laravel Hybrid Search result streams merging](art/banner.png)

Portable full-text search macros and Reciprocal Rank Fusion (RRF) for Laravel.
Use the same query API across PostgreSQL, MySQL, SQL Server, and SQLite, then
combine ranked results from separate search systems.

[![Tests](https://github.com/eloquage/laravel-hybrid-search/actions/workflows/run-tests.yml/badge.svg)](https://github.com/eloquage/laravel-hybrid-search/actions/workflows/run-tests.yml)
[![Latest Version](https://img.shields.io/packagist/v/illuma-law/laravel-hybrid-search)](https://packagist.org/packages/illuma-law/laravel-hybrid-search)
[![License](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

Requires PHP 8.3+ and Laravel 11, 12, or 13.

## Install

```bash
composer require illuma-law/laravel-hybrid-search
```

## Create and query a full-text index

The `hybridFullText` migration macro uses native full-text indexes on
PostgreSQL and MySQL. SQLite uses FTS5 with an external-content table and
triggers, and expects an integer `id` primary key. SQL Server requires you to
create a full-text catalog and index manually.

```php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

Schema::table('articles', function (Blueprint $table): void {
    $table->hybridFullText(['title', 'body'], 'articles_search');
});
```

Query with `whereHybridFullText` on a query builder or Eloquent model:

```php
$articles = Article::query()
    ->whereHybridFullText(['title', 'body'], 'laravel macros')
    ->limit(20)
    ->get();

// Exclude matching rows:
$otherArticles = Article::query()
    ->whereHybridFullText('title', 'deprecated', not: true)
    ->get();
```

Use `$table->dropHybridFullText('articles_search')` in the rollback migration.

## Combine ranked result lists

RRF combines ranked IDs from sources such as keyword and vector search. This
package does not generate embeddings or run vector searches; pass it the
ranked results from your search providers.

```php
use IllumaLaw\HybridSearch\ReciprocalRankFusion;

$scores = ReciprocalRankFusion::combine([
    'keyword' => collect([12, 8, 3]),
    'vector' => collect([8, 5, 12]),
]);

$rankedIds = $scores->keys(); // IDs sorted by descending RRF score
```

The default RRF constant is 60; pass `k:` to override it.

## Optional Scout helpers

Use `EnsuresScoutKeyIsString` when a Scout engine requires string keys. The
optional `ScoutEngineCheck` requires `spatie/laravel-health`:

```bash
composer require spatie/laravel-health
```

```php
use IllumaLaw\HybridSearch\HealthChecks\ScoutEngineCheck;
use Spatie\Health\Facades\Health;

Health::checks([ScoutEngineCheck::new()]);
```

The check supports Meilisearch, Typesense, and Algolia; local Scout drivers are
skipped.

## Test

```bash
composer test
composer analyse
vendor/bin/pint --test
```

MIT licensed. See [LICENSE](LICENSE).
