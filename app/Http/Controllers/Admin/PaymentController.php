<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Payments/Index', [
            'buyers' => Client::orderBy('name')->get(['id', 'name', 'phone', 'email']),
        ]);
    }

    public function records(Request $request): Response
    {
        return Inertia::render('Admin/Payments/Records', [
            'initialView' => $request->query('view', 'invoices'),
        ]);
    }

    public function record(Request $request): Response
    {
        $prefill = $request->hasAny(['ref', 'buyer', 'unit'])
            ? $request->only(['ref', 'buyer', 'unit', 'project', 'amount', 'ptype', 'method', 'status'])
            : [];

        return Inertia::render('Admin/Payments/Record', [
            'buyers'  => Client::orderBy('name')->get(['id', 'name', 'phone', 'email']),
            'prefill' => $prefill,
        ]);
    }
}
