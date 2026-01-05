<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ForceCacheClear extends Command
{
    protected $signature = 'cache:force-clear';
    protected $description = 'Force clear all caches including OPcache';

    public function handle()
    {
        $this->info('🔄 Forcing cache clear...');

        // Clear all Laravel caches
        $this->call('cache:clear');
        $this->call('config:clear');
        $this->call('route:clear');
        $this->call('view:clear');
        $this->call('optimize:clear');

        // Clear OPcache if available
        if (function_exists('opcache_reset')) {
            opcache_reset();
            $this->info('✅ OPcache cleared');
        } else {
            $this->warn('⚠️  OPcache not available in CLI');
        }

        // Verify Scanner component exists
        $scannerPath = app_path('Livewire/Banero/Pools/Scanner.php');
        if (file_exists($scannerPath)) {
            $content = file_get_contents($scannerPath);
            if (strpos($content, 'public function confirm') !== false) {
                $this->info('✅ Scanner component has confirm() method');
            } else {
                $this->error('❌ Scanner component missing confirm() method');
            }
        } else {
            $this->error('❌ Scanner component not found');
        }

        $this->info('✅ All caches cleared successfully');
        
        return 0;
    }
}
