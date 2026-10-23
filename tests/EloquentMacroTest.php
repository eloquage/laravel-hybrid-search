<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class TestModel extends Model
{
    protected $table = 'test_table_eloquent';
}

it('calls whereHybridFullText on eloquent builder', function () {
    Config::set('database.default', 'testing');

    Schema::create('test_table_eloquent', function (Blueprint $table) {
        $table->id();
        $table->string('title');
    });

    $query = TestModel::query()->whereHybridFullText(['title'], 'search query');

    expect(strtolower($query->toSql()))->toContain('test_table_eloquent_fts match ?');
});
