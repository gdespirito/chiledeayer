<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\Photo;
use App\Models\Place;
use App\Models\Tag;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Generate a dynamic XML sitemap.
     */
    public function __invoke(): Response
    {
        $baseUrl = config('app.url');

        $photos = Photo::query()
            ->select(['id', 'updated_at'])
            ->orderBy('id')
            ->get();

        $places = Place::query()
            ->select(['id', 'slug', 'updated_at'])
            ->orderBy('id')
            ->get();

        $tags = Tag::query()
            ->select(['id', 'slug', 'updated_at'])
            ->orderBy('id')
            ->get();

        $persons = Person::query()
            ->select(['id', 'slug', 'updated_at'])
            ->orderBy('id')
            ->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        // Static pages
        $xml .= $this->url($baseUrl.'/', 'daily', '1.0');
        $xml .= $this->url($baseUrl.'/photos', 'daily', '0.9');
        $xml .= $this->url($baseUrl.'/places', 'daily', '0.9');
        $xml .= $this->url($baseUrl.'/tags', 'daily', '0.9');
        $xml .= $this->url($baseUrl.'/persons', 'daily', '0.9');
        $xml .= $this->url($baseUrl.'/map', 'daily', '0.9');
        $xml .= $this->url($baseUrl.'/leaderboard', 'daily', '0.9');
        $xml .= $this->url($baseUrl.'/contribuir', 'monthly', '0.7');

        // Individual photos
        foreach ($photos as $photo) {
            $xml .= $this->url(
                $baseUrl.'/photos/'.$photo->id,
                'weekly',
                '0.8',
                $photo->updated_at?->toW3cString(),
            );
        }

        // Individual places
        foreach ($places as $place) {
            $xml .= $this->url(
                $baseUrl.'/places/'.$place->slug,
                'weekly',
                '0.8',
                $place->updated_at?->toW3cString(),
            );
        }

        // Individual tags
        foreach ($tags as $tag) {
            $xml .= $this->url(
                $baseUrl.'/tags/'.$tag->slug,
                'weekly',
                '0.8',
                $tag->updated_at?->toW3cString(),
            );
        }

        // Individual persons
        foreach ($persons as $person) {
            $xml .= $this->url(
                $baseUrl.'/persons/'.$person->slug,
                'weekly',
                '0.8',
                $person->updated_at?->toW3cString(),
            );
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }

    /**
     * Generate a single <url> element.
     */
    private function url(string $loc, string $changefreq, string $priority, ?string $lastmod = null): string
    {
        $entry = "  <url>\n";
        $entry .= "    <loc>{$loc}</loc>\n";
        if ($lastmod) {
            $entry .= "    <lastmod>{$lastmod}</lastmod>\n";
        }
        $entry .= "    <changefreq>{$changefreq}</changefreq>\n";
        $entry .= "    <priority>{$priority}</priority>\n";
        $entry .= "  </url>\n";

        return $entry;
    }
}
