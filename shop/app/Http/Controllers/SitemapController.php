<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Sitemap\GetSitemapUrls;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(GetSitemapUrls $getSitemapUrls): Response
    {
        $lines = ['<?xml version="1.0" encoding="UTF-8"?>'];
        $lines[] = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($getSitemapUrls() as $url) {
            $lines[] = '  <url>';
            $lines[] = '    <loc>'.htmlspecialchars($url['loc'], ENT_XML1).'</loc>';

            if ($url['lastmod'] !== null) {
                $lines[] = '    <lastmod>'.$url['lastmod'].'</lastmod>';
            }

            $lines[] = '  </url>';
        }

        $lines[] = '</urlset>';

        return response(implode("\n", $lines)."\n")
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots(): Response
    {
        $base = rtrim((string) config('app.url'), '/');

        $body = implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Sitemap: '.$base.'/sitemap.xml',
        ])."\n";

        return response($body)->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
