<?php

namespace App\Providers;

use DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider {
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() {
        DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('int2vector', 'smallint');
        DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('_int2vector', 'smallint');
        DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('_int2', 'smallint');
        DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('macaddr', 'string');
        DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('cidr', 'string');
        DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('_float4', 'float');
        DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('_float8', 'float');
        DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('_jsonb', 'string');
    }
}
