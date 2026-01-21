<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Page;

/**
 * Pages Controller
 *
 * Handles dynamic content pages from database.
 */
class PageController extends Controller
{
    /**
     * Show a page by slug
     */
    public function show(string $slug): Response
    {
        $page = Page::findActiveBySlug($slug);

        if (!$page) {
            return $this->notFound();
        }

        // Parse content placeholders
        $content = Page::parseContent($page['content'] ?? '');

        return $this->view('frontend/pages/dynamic', [
            'title' => $page['title'],
            'meta_description' => Page::parseContent($page['meta_description'] ?? ''),
            'page' => $page,
            'content' => $content,
        ]);
    }

    /**
     * Legacy route handlers - redirect to dynamic page
     */
    public function terms(): Response
    {
        return $this->show('terms');
    }

    public function privacy(): Response
    {
        return $this->show('privacy');
    }

    public function dmca(): Response
    {
        return $this->show('dmca');
    }

    public function cookies(): Response
    {
        return $this->show('cookies');
    }

    public function disclaimer(): Response
    {
        return $this->show('disclaimer');
    }

    public function contact(): Response
    {
        return $this->show('contact');
    }

    public function about(): Response
    {
        return $this->show('about');
    }

    /**
     * PWA Offline page
     */
    public function offline(): Response
    {
        ob_start();
        include \ROOT_PATH . '/app/Views/frontend/offline.php';
        $content = ob_get_clean();

        return Response::make($content, 200, [
            'Content-Type' => 'text/html',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
