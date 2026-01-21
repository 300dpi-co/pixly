<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Response;

class TagController extends Controller
{
    public function index(): Response
    {
        $db = $this->db();
        $tags = $db->fetchAll("SELECT * FROM tags ORDER BY usage_count DESC, name LIMIT 100");

        return $this->view('admin/tags/index', [
            'title' => 'Tags',
            'currentPage' => 'tags',
            'tags' => $tags,
        ], 'admin');
    }
}
