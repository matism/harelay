<?php

namespace App\Console\Commands;

use App\Models\HaConnection;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateTestConnection extends Command
{
    protected $signature = 'tunnel:create-test
                            {--email=test@example.com : Email for the test user}
                            {--subdomain= : Custom subdomain (random if not provided)}';

    protected $description = 'Create a test user and connection for development';

    public function handle(): int
    {
        $email = $this->option('email');
        $subdomain = $this->option('subdomain');

        // Create or find user
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $this->info("User: {$user->email} (password: 'password')");

        // Check if user already has a connection
        if ($user->haConnection) {
            $this->warn('User already has a connection.');
            $this->info("Subdomain: {$user->haConnection->subdomain}");

            if ($this->confirm('Would you like to regenerate the token?', true)) {
                $plainToken = HaConnection::generateConnectionToken();
                $user->haConnection->update([
                    'connection_token' => Hash::make($plainToken),
                ]);
                $this->newLine();
                $this->info('New connection token (save this!):');
                $this->line($plainToken);
            }

            return 0;
        }

        // Generate subdomain and token
        $subdomain = $subdomain ?: HaConnection::generateSubdomain();
        $plainToken = HaConnection::generateConnectionToken();

        // Create connection
        $connection = $user->haConnection()->create([
            'subdomain' => $subdomain,
            'connection_token' => Hash::make($plainToken),
            'status' => 'disconnected',
        ]);

        $this->newLine();
        $this->info('Connection created successfully!');
        $this->newLine();

        $this->table(
            ['Setting', 'Value'],
            [
                ['Subdomain', $connection->subdomain],
                ['Full URL', "{$connection->subdomain}.".config('app.proxy_domain')],
                ['Status', 'disconnected'],
            ]
        );

        $this->newLine();
        $this->warn("Connection Token (save this - it won't be shown again!):");
        $this->line($plainToken);

        $this->newLine();
        $this->info('To test the tunnel, run:');
        $this->line("php artisan tunnel:simulate {$connection->subdomain} \"{$plainToken}\"");

        return 0;
    }
}
