<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Response;

class AdsController extends Controller
{
    public function index(): Response
    {
        $db = $this->db();
        $placements = $db->fetchAll("SELECT * FROM ad_placements ORDER BY sort_order, name");

        return $this->view('admin/ads/index', [
            'title' => 'Ad Management',
            'currentPage' => 'ads',
            'placements' => $placements,
        ], 'admin');
    }
}
