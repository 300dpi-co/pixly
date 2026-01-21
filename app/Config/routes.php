<?php

declare(strict_types=1);

/**
 * Application Routes
 *
 * Define all routes for the application.
 */

use App\Core\Router;

/** @var Router $router */

/*
|--------------------------------------------------------------------------
| Installation Routes (no auth required)
|--------------------------------------------------------------------------
*/

$router->get('/install', 'App\Controllers\Install\InstallController@welcome');
$router->get('/install/database', 'App\Controllers\Install\InstallController@database');
$router->post('/install/database', 'App\Controllers\Install\InstallController@databaseSave', ['csrf']);
$router->get('/install/setup', 'App\Controllers\Install\InstallController@setup');
$router->post('/install/setup', 'App\Controllers\Install\InstallController@runSetup', ['csrf']);
$router->get('/install/admin', 'App\Controllers\Install\InstallController@admin');
$router->post('/install/admin', 'App\Controllers\Install\InstallController@adminSave', ['csrf']);
$router->get('/install/settings', 'App\Controllers\Install\InstallController@settings');
$router->post('/install/settings', 'App\Controllers\Install\InstallController@settingsSave', ['csrf']);
$router->get('/install/complete', 'App\Controllers\Install\InstallController@complete');

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

$router->get('/', 'App\Controllers\Frontend\HomeController@index');
$router->get('/gallery', 'App\Controllers\Frontend\GalleryController@index');
$router->get('/gallery/page/{page}', 'App\Controllers\Frontend\GalleryController@index');
$router->get('/image/{slug}', 'App\Controllers\Frontend\ImageController@show');
$router->get('/category/{slug}', 'App\Controllers\Frontend\CategoryController@index');
$router->get('/category/{slug}/page/{page}', 'App\Controllers\Frontend\CategoryController@index');
$router->get('/tag/{slug}', 'App\Controllers\Frontend\TagController@index');
$router->get('/tag/{slug}/page/{page}', 'App\Controllers\Frontend\TagController@index');
$router->get('/search', 'App\Controllers\Frontend\SearchController@index');
$router->get('/trending', 'App\Controllers\Frontend\TrendingController@index');
$router->get('/random', 'App\Controllers\Frontend\GalleryController@random');

// PWA Offline Page
$router->get('/offline', 'App\Controllers\Frontend\PageController@offline');

// Legal & Info Pages
$router->get('/terms', 'App\Controllers\Frontend\PageController@terms');
$router->get('/privacy', 'App\Controllers\Frontend\PageController@privacy');
$router->get('/dmca', 'App\Controllers\Frontend\PageController@dmca');
$router->get('/cookies', 'App\Controllers\Frontend\PageController@cookies');
$router->get('/disclaimer', 'App\Controllers\Frontend\PageController@disclaimer');
$router->get('/contact', 'App\Controllers\Frontend\PageController@contact');
$router->get('/about', 'App\Controllers\Frontend\PageController@about');

// Premium Subscription
$router->get('/premium', 'App\Controllers\Frontend\PremiumController@index');
$router->get('/premium/checkout', 'App\Controllers\Frontend\PremiumController@checkout');
$router->post('/premium/process-payment', 'App\Controllers\Frontend\PremiumController@processPayment', ['auth', 'csrf']);
$router->post('/premium/cancel', 'App\Controllers\Frontend\PremiumController@cancel', ['auth', 'csrf']);

// SEO
$router->get('/sitemap.xml', 'App\Controllers\Frontend\SitemapController@index');
$router->get('/sitemap-images.xml', 'App\Controllers\Frontend\SitemapController@images');
$router->get('/sitemap-blog.xml', 'App\Controllers\Frontend\SitemapController@blog');

/*
|--------------------------------------------------------------------------
| Blog Routes
|--------------------------------------------------------------------------
*/

$router->get('/blog', 'App\Controllers\Frontend\BlogController@index');
$router->get('/blog/page/{page}', 'App\Controllers\Frontend\BlogController@index');
$router->get('/blog/search', 'App\Controllers\Frontend\BlogController@search');
$router->get('/blog/category/{slug}', 'App\Controllers\Frontend\BlogController@category');
$router->get('/blog/category/{slug}/page/{page}', 'App\Controllers\Frontend\BlogController@category');
$router->get('/blog/tag/{slug}', 'App\Controllers\Frontend\BlogController@tag');
$router->get('/blog/tag/{slug}/page/{page}', 'App\Controllers\Frontend\BlogController@tag');
$router->get('/blog/{slug}', 'App\Controllers\Frontend\BlogController@show');
$router->post('/blog/{slug}/comment', 'App\Controllers\Frontend\BlogController@comment', ['csrf']);

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

$router->get('/login', 'App\Controllers\Auth\LoginController@show');
$router->post('/login', 'App\Controllers\Auth\LoginController@login', ['csrf']);
$router->get('/register', 'App\Controllers\Auth\RegisterController@show');
$router->post('/register', 'App\Controllers\Auth\RegisterController@register', ['csrf']);
$router->get('/logout', 'App\Controllers\Auth\LogoutController@logout', ['auth']);
$router->get('/forgot-password', 'App\Controllers\Auth\PasswordController@forgot');
$router->post('/forgot-password', 'App\Controllers\Auth\PasswordController@sendReset', ['csrf']);
$router->get('/reset-password/{token}', 'App\Controllers\Auth\PasswordController@reset');
$router->post('/reset-password', 'App\Controllers\Auth\PasswordController@update', ['csrf']);

/*
|--------------------------------------------------------------------------
| Public Profile Route
|--------------------------------------------------------------------------
*/

$router->get('/user/{username}', 'App\Controllers\Frontend\ProfileController@publicProfile');

/*
|--------------------------------------------------------------------------
| User Routes (Authenticated)
|--------------------------------------------------------------------------
*/

$router->get('/profile', 'App\Controllers\Frontend\ProfileController@settings', ['auth']);
$router->post('/profile', 'App\Controllers\Frontend\ProfileController@updateProfile', ['auth', 'csrf']);
$router->post('/profile/avatar', 'App\Controllers\Frontend\ProfileController@updateAvatar', ['auth', 'csrf']);
$router->post('/profile/password', 'App\Controllers\Frontend\ProfileController@updatePassword', ['auth', 'csrf']);
$router->get('/favorites', 'App\Controllers\Frontend\FavoriteController@index', ['auth']);
$router->get('/my-likes', 'App\Controllers\Frontend\FavoriteController@likes', ['auth']);
$router->get('/my-saves', 'App\Controllers\Frontend\FavoriteController@saves', ['auth']);
$router->get('/upload', 'App\Controllers\Frontend\UploadController@show', ['auth']);
$router->post('/upload', 'App\Controllers\Frontend\UploadController@upload', ['auth', 'csrf']);

// Contributor Routes
$router->get('/contributor/request', 'App\Controllers\Frontend\ContributorController@request', ['auth']);
$router->post('/contributor/request', 'App\Controllers\Frontend\ContributorController@submit', ['auth', 'csrf']);
$router->get('/contributor/status', 'App\Controllers\Frontend\ContributorController@status', ['auth']);

// API - Interactions (like/save/appreciate)
$router->post('/api/like', 'App\Controllers\Api\InteractionController@like', ['csrf']); // Likes work for guests too
$router->post('/api/save', 'App\Controllers\Api\InteractionController@save', ['auth', 'csrf']);
$router->post('/api/appreciate', 'App\Controllers\Api\InteractionController@appreciate', ['auth', 'csrf']); // Pexels theme
$router->post('/api/interaction-status', 'App\Controllers\Api\InteractionController@status', ['auth']);

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

$router->group(['prefix' => 'api'], function (Router $router) {
    $router->get('/images', 'App\Controllers\Api\ImageApiController@index');
    $router->get('/images/{slug}', 'App\Controllers\Api\ImageApiController@show');
    $router->get('/search', 'App\Controllers\Api\SearchApiController@index');

    // Authenticated API routes
    $router->post('/favorites/{id}', 'App\Controllers\Api\FavoriteApiController@store', ['auth', 'csrf']);
    $router->delete('/favorites/{id}', 'App\Controllers\Api\FavoriteApiController@destroy', ['auth', 'csrf']);
    $router->post('/comments', 'App\Controllers\Api\CommentApiController@store', ['auth', 'csrf']);

    // Reports (no auth required - anyone can report)
    $router->post('/reports', 'App\Controllers\Api\ReportApiController@store', ['csrf']);
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

$router->group(['prefix' => 'admin', 'middleware' => ['auth', 'admin']], function (Router $router) {
    $router->get('/', 'App\Controllers\Admin\DashboardController@index');

    // Images
    $router->get('/images', 'App\Controllers\Admin\ImageController@index');
    $router->get('/images/upload', 'App\Controllers\Admin\UploadController@show');
    $router->post('/images/upload', 'App\Controllers\Admin\UploadController@upload', ['csrf']);
    $router->post('/images/bulk', 'App\Controllers\Admin\ImageController@bulk', ['csrf']);
    $router->get('/images/{id}/edit', 'App\Controllers\Admin\ImageController@edit');
    $router->post('/images/{id}', 'App\Controllers\Admin\ImageController@update', ['csrf']);
    $router->post('/images/{id}/delete', 'App\Controllers\Admin\ImageController@destroy', ['csrf']);
    $router->delete('/images/{id}', 'App\Controllers\Admin\ImageController@destroy', ['csrf']);
    $router->post('/images/{id}/analyze', 'App\Controllers\Admin\ImageController@analyze', ['csrf']);
    $router->post('/images/{id}/auto-fill', 'App\Controllers\Admin\ImageController@autoFill', ['csrf']);

    // Categories
    $router->get('/categories', 'App\Controllers\Admin\CategoryController@index');
    $router->get('/categories/create', 'App\Controllers\Admin\CategoryController@create');
    $router->post('/categories', 'App\Controllers\Admin\CategoryController@store', ['csrf']);
    $router->get('/categories/{id}/edit', 'App\Controllers\Admin\CategoryController@edit');
    $router->post('/categories/{id}', 'App\Controllers\Admin\CategoryController@update', ['csrf']);
    $router->post('/categories/{id}/delete', 'App\Controllers\Admin\CategoryController@destroy', ['csrf']);
    $router->post('/categories/{id}/toggle', 'App\Controllers\Admin\CategoryController@toggleActive', ['csrf']);
    $router->post('/categories/order', 'App\Controllers\Admin\CategoryController@updateOrder', ['csrf']);

    // Tags
    $router->get('/tags', 'App\Controllers\Admin\TagController@index');

    // Users
    $router->get('/users', 'App\Controllers\Admin\UserController@index');
    $router->get('/users/create', 'App\Controllers\Admin\UserController@create');
    $router->post('/users', 'App\Controllers\Admin\UserController@store', ['csrf']);
    $router->get('/users/{id}', 'App\Controllers\Admin\UserController@show');
    $router->get('/users/{id}/edit', 'App\Controllers\Admin\UserController@edit');
    $router->post('/users/{id}', 'App\Controllers\Admin\UserController@update', ['csrf']);
    $router->post('/users/{id}/delete', 'App\Controllers\Admin\UserController@destroy', ['csrf']);
    $router->post('/users/{id}/status', 'App\Controllers\Admin\UserController@updateStatus', ['csrf']);
    $router->post('/users/{id}/role', 'App\Controllers\Admin\UserController@updateRole', ['csrf']);

    // Contributors
    $router->get('/contributors', 'App\Controllers\Admin\ContributorController@index');
    $router->post('/contributors/{id}/approve', 'App\Controllers\Admin\ContributorController@approve', ['csrf']);
    $router->post('/contributors/{id}/reject', 'App\Controllers\Admin\ContributorController@reject', ['csrf']);

    // Moderation
    $router->get('/moderation', 'App\Controllers\Admin\ModerationController@index');
    $router->post('/moderation/{id}/approve', 'App\Controllers\Admin\ModerationController@approve', ['csrf']);
    $router->post('/moderation/{id}/reject', 'App\Controllers\Admin\ModerationController@reject', ['csrf']);
    $router->post('/moderation/bulk-approve', 'App\Controllers\Admin\ModerationController@bulkApprove', ['csrf']);
    $router->post('/moderation/bulk-reject', 'App\Controllers\Admin\ModerationController@bulkReject', ['csrf']);
    $router->post('/moderation/trusted', 'App\Controllers\Admin\ModerationController@addTrusted', ['csrf']);
    $router->post('/moderation/trusted/{id}/remove', 'App\Controllers\Admin\ModerationController@removeTrusted', ['csrf']);

    // AI
    $router->get('/ai', 'App\Controllers\Admin\AIController@index');
    $router->post('/ai/process', 'App\Controllers\Admin\AIController@process', ['csrf']);
    $router->post('/ai/process/{id}', 'App\Controllers\Admin\AIController@processSingle', ['csrf']);
    $router->post('/ai/queue', 'App\Controllers\Admin\AIController@queue', ['csrf']);
    $router->post('/ai/clear-failed', 'App\Controllers\Admin\AIController@clearFailed', ['csrf']);
    $router->post('/ai/retry-failed', 'App\Controllers\Admin\AIController@retryFailed', ['csrf']);

    // Trends & Analytics
    $router->get('/trends', 'App\Controllers\Admin\TrendsController@index');
    $router->get('/analytics', 'App\Controllers\Admin\AnalyticsController@index');
    $router->post('/analytics/google', 'App\Controllers\Admin\AnalyticsController@updateGoogleAnalytics', ['csrf']);

    // Ads
    $router->get('/ads', 'App\Controllers\Admin\AdsController@index');

    // Settings
    $router->get('/settings', 'App\Controllers\Admin\SettingsController@index');
    $router->post('/settings', 'App\Controllers\Admin\SettingsController@update', ['csrf']);
    $router->get('/settings/create', 'App\Controllers\Admin\SettingsController@create');
    $router->post('/settings/store', 'App\Controllers\Admin\SettingsController@store', ['csrf']);
    $router->post('/settings/{id}/delete', 'App\Controllers\Admin\SettingsController@destroy', ['csrf']);
    $router->post('/settings/logo/upload', 'App\Controllers\Admin\SettingsController@uploadLogo', ['csrf']);
    $router->post('/settings/logo/delete', 'App\Controllers\Admin\SettingsController@deleteLogo', ['csrf']);

    // Blog
    $router->get('/blog', 'App\Controllers\Admin\BlogController@index');
    $router->get('/blog/create', 'App\Controllers\Admin\BlogController@create');
    $router->post('/blog', 'App\Controllers\Admin\BlogController@store', ['csrf']);
    $router->get('/blog/{id}/edit', 'App\Controllers\Admin\BlogController@edit');
    $router->post('/blog/{id}', 'App\Controllers\Admin\BlogController@update', ['csrf']);
    $router->post('/blog/{id}/delete', 'App\Controllers\Admin\BlogController@destroy', ['csrf']);

    // Blog AI
    $router->post('/blog/ai/generate', 'App\Controllers\Admin\BlogController@aiGenerate', ['csrf']);
    $router->post('/blog/ai/improve', 'App\Controllers\Admin\BlogController@aiImprove', ['csrf']);

    // Blog Categories
    $router->get('/blog/categories', 'App\Controllers\Admin\BlogController@categories');
    $router->post('/blog/categories', 'App\Controllers\Admin\BlogController@storeCategory', ['csrf']);
    $router->post('/blog/categories/{id}', 'App\Controllers\Admin\BlogController@updateCategory', ['csrf']);
    $router->post('/blog/categories/{id}/delete', 'App\Controllers\Admin\BlogController@destroyCategory', ['csrf']);

    // Blog Comments
    $router->get('/blog/comments', 'App\Controllers\Admin\BlogController@comments');
    $router->post('/blog/comments/{id}/approve', 'App\Controllers\Admin\BlogController@approveComment', ['csrf']);
    $router->post('/blog/comments/{id}/spam', 'App\Controllers\Admin\BlogController@spamComment', ['csrf']);
    $router->post('/blog/comments/{id}/delete', 'App\Controllers\Admin\BlogController@destroyComment', ['csrf']);

    // Marketing
    $router->get('/marketing', 'App\Controllers\Admin\MarketingController@index');

    // Ad Placements
    $router->get('/marketing/placements', 'App\Controllers\Admin\MarketingController@placements');
    $router->get('/marketing/placements/create', 'App\Controllers\Admin\MarketingController@createPlacement');
    $router->post('/marketing/placements', 'App\Controllers\Admin\MarketingController@storePlacement', ['csrf']);
    $router->get('/marketing/placements/{id}/edit', 'App\Controllers\Admin\MarketingController@editPlacement');
    $router->post('/marketing/placements/{id}', 'App\Controllers\Admin\MarketingController@updatePlacement', ['csrf']);
    $router->post('/marketing/placements/{id}/delete', 'App\Controllers\Admin\MarketingController@deletePlacement', ['csrf']);
    $router->post('/marketing/placements/{id}/toggle', 'App\Controllers\Admin\MarketingController@togglePlacement', ['csrf']);

    // Ads (Content)
    $router->get('/marketing/ads', 'App\Controllers\Admin\MarketingController@ads');
    $router->get('/marketing/ads/create', 'App\Controllers\Admin\MarketingController@createAd');
    $router->post('/marketing/ads', 'App\Controllers\Admin\MarketingController@storeAd', ['csrf']);
    $router->get('/marketing/ads/{id}/edit', 'App\Controllers\Admin\MarketingController@editAd');
    $router->post('/marketing/ads/{id}', 'App\Controllers\Admin\MarketingController@updateAd', ['csrf']);
    $router->post('/marketing/ads/{id}/delete', 'App\Controllers\Admin\MarketingController@deleteAd', ['csrf']);

    // Popup Ads
    $router->get('/marketing/popups', 'App\Controllers\Admin\MarketingController@popups');
    $router->get('/marketing/popups/create', 'App\Controllers\Admin\MarketingController@createPopup');
    $router->post('/marketing/popups', 'App\Controllers\Admin\MarketingController@storePopup', ['csrf']);
    $router->get('/marketing/popups/{id}/edit', 'App\Controllers\Admin\MarketingController@editPopup');
    $router->post('/marketing/popups/{id}', 'App\Controllers\Admin\MarketingController@updatePopup', ['csrf']);
    $router->post('/marketing/popups/{id}/delete', 'App\Controllers\Admin\MarketingController@deletePopup', ['csrf']);

    // Announcements
    $router->get('/marketing/announcements', 'App\Controllers\Admin\MarketingController@announcements');
    $router->get('/marketing/announcements/create', 'App\Controllers\Admin\MarketingController@createAnnouncement');
    $router->post('/marketing/announcements', 'App\Controllers\Admin\MarketingController@storeAnnouncement', ['csrf']);
    $router->get('/marketing/announcements/{id}/edit', 'App\Controllers\Admin\MarketingController@editAnnouncement');
    $router->post('/marketing/announcements/{id}', 'App\Controllers\Admin\MarketingController@updateAnnouncement', ['csrf']);
    $router->post('/marketing/announcements/{id}/delete', 'App\Controllers\Admin\MarketingController@deleteAnnouncement', ['csrf']);

    // Newsletter
    $router->get('/marketing/newsletter', 'App\Controllers\Admin\MarketingController@newsletter');
    $router->get('/marketing/newsletter/export', 'App\Controllers\Admin\MarketingController@exportSubscribers');
    $router->post('/marketing/newsletter/{id}/delete', 'App\Controllers\Admin\MarketingController@deleteSubscriber', ['csrf']);

    // Tracking & Social
    $router->get('/marketing/tracking', 'App\Controllers\Admin\MarketingController@tracking');
    $router->post('/marketing/tracking', 'App\Controllers\Admin\MarketingController@updateTracking', ['csrf']);
    $router->get('/marketing/social', 'App\Controllers\Admin\MarketingController@social');
    $router->post('/marketing/social', 'App\Controllers\Admin\MarketingController@updateSocial', ['csrf']);

    // Ad Settings
    $router->get('/marketing/ad-settings', 'App\Controllers\Admin\MarketingController@adSettings');
    $router->post('/marketing/ad-settings', 'App\Controllers\Admin\MarketingController@updateAdSettings', ['csrf']);

    // Pages
    $router->get('/pages', 'App\Controllers\Admin\PagesController@index');
    $router->get('/pages/create', 'App\Controllers\Admin\PagesController@create');
    $router->post('/pages', 'App\Controllers\Admin\PagesController@store', ['csrf']);
    $router->get('/pages/{id}/edit', 'App\Controllers\Admin\PagesController@edit');
    $router->post('/pages/{id}', 'App\Controllers\Admin\PagesController@update', ['csrf']);
    $router->post('/pages/{id}/delete', 'App\Controllers\Admin\PagesController@delete', ['csrf']);
    $router->post('/pages/{id}/toggle', 'App\Controllers\Admin\PagesController@toggle', ['csrf']);
});
