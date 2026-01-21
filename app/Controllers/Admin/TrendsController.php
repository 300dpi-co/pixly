<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Response;

class TrendsController extends Controller
{
    public function index(): Response
    {
        $db = $this->db();
        $trends = $db->fetchAll(
            "SELECT * FROM trending_keywords ORDER BY trend_score DESC LIMIT 50"
        );

        return $this->view('admin/trends/index', [
            'title' => 'Trends',
            'currentPage' => 'trends',
            'trends' => $trends,
        ], 'admin');
    }
}
