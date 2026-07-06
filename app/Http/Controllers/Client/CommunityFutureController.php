<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class CommunityFutureController extends Controller
{

    public function index()
    {
        return Inertia::render('Client/CommunityFuture', [
            'title' => 'Community & Future Vision',
        ]);
    }
}
