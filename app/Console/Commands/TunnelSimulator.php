<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TunnelSimulator extends Command
{
    protected $signature = 'tunnel:simulate
                            {subdomain : The subdomain to connect as}
                            {token : The connection token}
                            {--url=http://localhost:8000 : The HARelay server URL}
                            {--ha-url=http://localhost:8123 : The Home Assistant URL to forward requests to}
                            {--interval=2 : Polling interval in seconds}';

    protected $description = 'Simulate an HA add-on tunnel client for testing';

    private bool $running = true;

    public function handle(): int
    {
        $subdomain = $this->argument('subdomain');
        $token = $this->argument('token');
        $serverUrl = $this->option('url');
        $haUrl = $this->option('ha-url');
        $interval = (int) $this->option('interval');

        $this->info('HARelay Tunnel Simulator');
        $this->info('========================');
        $this->info("Subdomain: {$subdomain}");
        $this->info("Server: {$serverUrl}");
        $this->info("HA URL: {$haUrl}");
        $this->newLine();

        // Register signal handler for graceful shutdown
        if (extension_loaded('pcntl')) {
            pcntl_signal(SIGINT, function () {
                $this->running = false;
                $this->warn("\nShutting down...");
            });
        }

        // Connect to server
        $this->info('Connecting to HARelay...');
        $response = Http::post("{$serverUrl}/api/tunnel/connect", [
            'subdomain' => $subdomain,
            'token' => $token,
        ]);

        if (! $response->successful()) {
            $this->error('Failed to connect: '.$response->body());

            return 1;
        }

        $this->info('Connected successfully!');
        $data = $response->json();
        $this->info('WebSocket channel: '.($data['websocket']['channel'] ?? 'N/A'));
        $this->newLine();

        $this->info('Polling for requests (Ctrl+C to stop)...');
        $this->newLine();

        $lastHeartbeat = time();

        while ($this->running) {
            if (extension_loaded('pcntl')) {
                pcntl_signal_dispatch();
            }

            // Send heartbeat every 30 seconds
            if (time() - $lastHeartbeat >= 30) {
                Http::post("{$serverUrl}/api/tunnel/heartbeat", [
                    'subdomain' => $subdomain,
                    'token' => $token,
                ]);
                $lastHeartbeat = time();
                $this->line('<fg=gray>['.now()->format('H:i:s').'] Heartbeat sent</>');
            }

            // Poll for requests
            $pollResponse = Http::post("{$serverUrl}/api/tunnel/poll", [
                'subdomain' => $subdomain,
                'token' => $token,
            ]);

            if ($pollResponse->successful()) {
                $pollData = $pollResponse->json();

                if (! empty($pollData['request'])) {
                    $request = $pollData['request'];
                    $this->handleRequest($request, $subdomain, $token, $serverUrl, $haUrl);
                }
            }

            sleep($interval);
        }

        // Disconnect
        $this->info('Disconnecting...');
        Http::post("{$serverUrl}/api/tunnel/disconnect", [
            'subdomain' => $subdomain,
            'token' => $token,
        ]);

        $this->info('Disconnected. Goodbye!');

        return 0;
    }

    private function handleRequest(array $request, string $subdomain, string $token, string $serverUrl, string $haUrl): void
    {
        $requestId = $request['id'];
        $method = $request['method'];
        $uri = $request['uri'];

        $this->info('['.now()->format('H:i:s')."] Received request: {$method} {$uri}");

        try {
            // Forward request to Home Assistant
            $haResponse = Http::withHeaders($request['headers'] ?? [])
                ->timeout(25)
                ->send($method, $haUrl.$uri, [
                    'body' => $request['body'] ?? null,
                ]);

            $statusCode = $haResponse->status();
            $body = $haResponse->body();
            $headers = $haResponse->headers();

            // Flatten headers
            $flatHeaders = [];
            foreach ($headers as $name => $values) {
                $flatHeaders[$name] = is_array($values) ? implode(', ', $values) : $values;
            }

            $this->line("  -> Response: {$statusCode} (".strlen($body).' bytes)');

        } catch (\Exception $e) {
            $this->error('  -> Error forwarding request: '.$e->getMessage());

            $statusCode = 502;
            $body = 'Bad Gateway: Unable to reach Home Assistant';
            $flatHeaders = ['Content-Type' => 'text/plain'];
        }

        // Submit response back to HARelay
        $submitResponse = Http::post("{$serverUrl}/api/tunnel/response", [
            'subdomain' => $subdomain,
            'token' => $token,
            'request_id' => $requestId,
            'status_code' => $statusCode,
            'headers' => $flatHeaders,
            'body' => $body,
        ]);

        if ($submitResponse->successful()) {
            $this->line('  -> Response submitted successfully');
        } else {
            $this->error('  -> Failed to submit response: '.$submitResponse->body());
        }

        $this->newLine();
    }
}
