<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Artisan command to clear the permission cache.
 *
 * When roles and permissions are updated, the cache may serve stale
 * data until it expires. Running this command forces an immediate flush,
 * ensuring the application reflects the latest permission state.
 *
 * Usage:
 *   php artisan permission:clear
 */
class ClearPermissionCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clear-permission-cache-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * Flushes the entire application cache and outputs a confirmation
     * message. Should be run after any role or permission changes to
     * prevent stale data from being served.
     *
     * @return void
     */
    public function handle()
    {
        Cache::flush();

        $this->info('Permission cache cleared.');
    }
}
