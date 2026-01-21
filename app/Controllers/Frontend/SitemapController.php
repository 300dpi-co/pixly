<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Core\Controller;
use App\Core\Response;
use App\Services\SitemapGenerator;

/**
 * Sitemap Controller
 *
 * Serves XML sitemaps.
 */
class SitemapController extends Controller
{
    /**
     * Serve main sitemap
     */
    public function index(): Response
    {
        $generator = new SitemapGenerator();
        $xml = $generator->generate();

        return Response::xml($xml);
    }

    /**
     * Serve images sitemap
     */
    public function images(): Response
    {
        $generator = new SitemapGenerator();
        $xml = $generator->generateImagesSitemap();

        return Response::xml($xml);
    }

    /**
     * Serve blog sitemap
     */
    public function blog(): Response
    {
        $generator = new SitemapGenerator();
        $xml = $generator->generateBlogSitemap();

        return Response::xml($xml);
    }
}
