<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CheckUserUnits extends Command
{
    protected $signature = 'user:check-units {email}';
    protected $description = 'Check if a user has active unit assignments';

    public function handle()
    {
        $email = $this->argument('email');
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("❌ User not found: {$email}");
            return 1;
        }
        
        $this->info("✅ User found: {$user->name}");
        
        $unitUsers = $user->currentUnitUsers()->with('unit.building.complex')->get();
        
        if ($unitUsers->isEmpty()) {
            $this->error("❌ No active unit assignments found");
            return 1;
        }
        
        $this->info("✅ Active unit assignments: {$unitUsers->count()}");
        
        foreach ($unitUsers as $unitUser) {
            $unit = $unitUser->unit;
            $this->line("   • {$unit->full_identifier} - {$unit->building->complex->name}");
            $this->line("     Role: {$unitUser->role}");
            $this->line("     Started: {$unitUser->started_at->format('Y-m-d')}");
            $this->line("     Ended: " . ($unitUser->ended_at ? $unitUser->ended_at->format('Y-m-d') : 'Active'));
        }
        
        return 0;
    }
}
