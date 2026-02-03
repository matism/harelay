<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\View\View;

class MarketingController extends Controller
{
    /**
     * Marketing pages with their metadata for sitemap/llms.txt generation.
     */
    private function getPages(): array
    {
        return [
            [
                'route' => 'marketing.home',
                'priority' => '1.0',
                'changefreq' => 'weekly',
                'title' => 'HARelay - Secure Remote Access for Home Assistant',
                'description' => 'Access your Home Assistant from anywhere without port forwarding. Secure WebSocket tunnel with easy setup.',
            ],
            [
                'route' => 'marketing.how-it-works',
                'priority' => '0.9',
                'changefreq' => 'monthly',
                'title' => 'How It Works',
                'description' => 'Learn how HARelay provides secure remote access to your Home Assistant in three simple steps.',
            ],
            [
                'route' => 'marketing.security',
                'priority' => '0.9',
                'changefreq' => 'monthly',
                'title' => 'Security',
                'description' => 'Learn how HARelay keeps your Home Assistant secure. No open ports, encrypted connections, and zero access to your data.',
            ],
            [
                'route' => 'marketing.vs-nabu-casa',
                'priority' => '0.7',
                'changefreq' => 'monthly',
                'title' => 'Nabu Casa vs HARelay',
                'description' => 'Compare HARelay and Nabu Casa for Home Assistant remote access.',
            ],
            [
                'route' => 'marketing.vs-homeflow',
                'priority' => '0.7',
                'changefreq' => 'monthly',
                'title' => 'Homeflow.io vs HARelay',
                'description' => 'Compare HARelay and Homeflow.io for Home Assistant remote access.',
            ],
            [
                'route' => 'marketing.privacy',
                'priority' => '0.5',
                'changefreq' => 'yearly',
                'title' => 'Privacy Policy',
                'description' => 'Learn how HARelay handles your data and protects your privacy.',
            ],
            [
                'route' => 'marketing.imprint',
                'priority' => '0.3',
                'changefreq' => 'yearly',
                'title' => 'Imprint',
                'description' => 'Legal information and contact details for HARelay.',
            ],
        ];
    }

    public function home(): View
    {
        return view('marketing.home');
    }

    public function howItWorks(): View
    {
        return view('marketing.how-it-works');
    }

    public function privacy(): View
    {
        return view('marketing.privacy');
    }

    public function security(): View
    {
        return view('marketing.security');
    }

    public function imprint(): View
    {
        return view('marketing.imprint');
    }

    public function vsNabuCasa(): View
    {
        return view('marketing.vs-nabu-casa');
    }

    public function vsHomeflow(): View
    {
        return view('marketing.vs-homeflow');
    }

    /**
     * Generate sitemap.xml dynamically.
     */
    public function sitemap(): Response
    {
        $pages = $this->getPages();
        $lastmod = date('Y-m-d');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($pages as $page) {
            $xml .= '  <url>'."\n";
            $xml .= '    <loc>'.route($page['route']).'</loc>'."\n";
            $xml .= '    <lastmod>'.$lastmod.'</lastmod>'."\n";
            $xml .= '    <changefreq>'.$page['changefreq'].'</changefreq>'."\n";
            $xml .= '    <priority>'.$page['priority'].'</priority>'."\n";
            $xml .= '  </url>'."\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }

    /**
     * Generate llms.txt for LLM crawlers.
     */
    public function llmsTxt(): Response
    {
        $pages = $this->getPages();
        $baseUrl = config('app.url');

        $content = <<<TXT
# HARelay

> Secure remote access to Home Assistant without port forwarding.

HARelay is a free service that provides secure remote access to Home Assistant smart home systems. It eliminates the need for port forwarding, dynamic DNS, or complex network configuration.

## Key Features

- **No Port Forwarding**: Your Home Assistant stays behind your firewall with no exposed ports
- **Free Service**: Completely free to use, no subscription required
- **Easy Setup**: Install add-on, enter pairing code, done in under 5 minutes
- **Secure**: TLS 1.3 encryption, A+ SSL Labs rating, servers in Germany (GDPR compliant)
- **No Traffic Logging**: We relay traffic but never log or store it
- **Works Everywhere**: Works behind CGNAT, strict firewalls, and any network configuration

## How It Works

1. Create a free account at harelay.com
2. Install the HARelay add-on from Home Assistant's Add-on Store (repository: https://github.com/harelay/ha-addon)
3. Enter the pairing code displayed by the add-on
4. Access your Home Assistant at your-subdomain.harelay.com

## Technical Details

- **Protocol**: WebSocket tunnel with MessagePack binary encoding
- **Encryption**: TLS 1.3 with forward secrecy, HSTS enabled
- **Server Location**: Germany (EU, GDPR compliant)
- **Latency**: Minimal overhead, real-time features work seamlessly
- **Compatibility**: Works with all Home Assistant features including dashboards, automations, and add-ons

## Comparison with Alternatives

### vs Nabu Casa
- HARelay: Free, focused on remote access only
- Nabu Casa: Paid subscription (\$6.50/month), includes voice assistants and supports HA development

### vs Port Forwarding
- HARelay: No open ports, invisible to attackers, works behind CGNAT
- Port Forwarding: Exposes network, requires static IP or DDNS, security risk

## Contact

- Website: {$baseUrl}
- Email: mathias@harelay.com
- Location: Graz, Austria

## Pages


TXT;

        foreach ($pages as $page) {
            $content .= '- ['.$page['title'].']('.route($page['route']).'): '.$page['description']."\n";
        }

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
        ]);
    }
}
