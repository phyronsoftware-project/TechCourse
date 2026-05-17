<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class ReportController extends Controller
{
    public function index()
    {
        return view('admin.pages.reports.index', [
            'pageTitle' => 'Reports',
        ]);
    }
}
