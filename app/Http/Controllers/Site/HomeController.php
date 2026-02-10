<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Service;

class HomeController extends Controller
{
    public function index()
    {
        $banners = Banner::query()
            ->where('is_active', true)
            ->orderBy('position')
            ->get(['id','name','image_path','position']);

        $services = Service::query()
            ->where('is_active', true)
            ->orderBy('position')
            ->get();

        return view('home', compact('banners', 'services'));
    }
}
