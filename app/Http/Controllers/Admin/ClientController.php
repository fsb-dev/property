<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ClientRequest\StoreClientRequest;
use App\Http\Requests\Admin\ClientRequest\UpdateClientRequest;
use App\Models\Client;
use App\Services\ClientService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClientController extends Controller
{
    public function __construct(private ClientService $service) {}

    public function index(Request $request)
    {
        return Inertia::render('Admin/Clients/Index', [
            'clients'  => $this->service->paginate($request),
            'stats'    => $this->service->stats(),
            'enums'    => $this->service->enums(),
            'filters'  => $request->only('search', 'status', 'source'),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Clients/Create', [
            'enums' => $this->service->enums(),
        ]);
    }

    public function store(StoreClientRequest $request)
    {
        $this->service->create($request->validated());

        return redirect()->route('admin.clients.index')
            ->with('success', 'Client created successfully.');
    }

    public function show(Client $client)
    {
        return Inertia::render('Admin/Clients/Show', [
            'client' => $this->service->forShow($client),
            'enums'  => $this->service->enums(),
        ]);
    }

    public function edit(Client $client)
    {
        return Inertia::render('Admin/Clients/Edit', [
            'client' => $this->service->forEdit($client),
            'enums'  => $this->service->enums(),
        ]);
    }

    public function update(UpdateClientRequest $request, Client $client)
    {
        $this->service->update($client, $request->validated());

        return redirect()->route('admin.clients.show', $client)
            ->with('success', 'Client updated successfully.');
    }

    public function destroy(Client $client)
    {
        $name = $this->service->delete($client);

        return redirect()->route('admin.clients.index')
            ->with('success', "{$name} has been removed.");
    }
}
