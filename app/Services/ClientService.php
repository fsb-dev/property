<?php

namespace App\Services;

use App\Enums\ClientSource;
use App\Enums\ClientStatus;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ClientService
{
    public function paginate(Request $request): LengthAwarePaginator
    {
        return Client::query()
            ->when($request->search, fn($q) =>
                $q->where('name',  'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%")
            )
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->source, fn($q) => $q->where('source', $request->source))
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn(Client $c) => [
                'id'         => $c->id,
                'name'       => $c->name,
                'email'      => $c->email,
                'phone'      => $c->phone,
                'source'     => $c->source?->value,
                'source_label' => $c->source?->label(),
                'status'     => $c->status->value,
                'status_label' => $c->status->label(),
                'status_color' => $c->status->color(),
                'avatar'     => $c->getFirstMediaUrl('avatar', 'thumb') ?: $c->getFirstMediaUrl('avatar'),
                'created_at' => $c->created_at->format('d M Y'),
            ]);
    }

    public function stats(): array
    {
        return [
            'total'       => Client::count(),
            'active'      => Client::where('status', ClientStatus::Active)->count(),
            'inactive'    => Client::where('status', ClientStatus::Inactive)->count(),
            'blacklisted' => Client::where('status', ClientStatus::Blacklisted)->count(),
        ];
    }

    public function enums(): array
    {
        return [
            'statuses' => collect(ClientStatus::cases())->map(fn($e) => [
                'value' => $e->value,
                'label' => $e->label(),
                'color' => $e->color(),
            ]),
            'sources' => collect(ClientSource::cases())->map(fn($e) => [
                'value' => $e->value,
                'label' => $e->label(),
            ]),
            'genders' => [
                ['value' => 'male',   'label' => 'Male'],
                ['value' => 'female', 'label' => 'Female'],
                ['value' => 'other',  'label' => 'Other'],
            ],
        ];
    }

    public function forEdit(Client $client): array
    {
        return [
            'id'           => $client->id,
            'name'         => $client->name,
            'email'        => $client->email,
            'phone'        => $client->phone,
            'gender'       => $client->gender,
            'date_of_birth'=> $client->date_of_birth?->format('Y-m-d'),
            'nationality'  => $client->nationality,
            'nid'          => $client->nid,
            'passport_no'  => $client->passport_no,
            'occupation'   => $client->occupation,
            'address'      => $client->address,
            'source'       => $client->source?->value,
            'notes'        => $client->notes,
            'status'       => $client->status->value,
            'avatar'       => $client->getFirstMediaUrl('avatar'),
            'kyc_documents'=> $client->getMedia('kyc_documents')->map(fn($m) => [
                'id'   => $m->id,
                'url'  => $m->getUrl(),
                'name' => $m->file_name,
                'size' => $m->size,
                'mime' => $m->mime_type,
            ])->toArray(),
        ];
    }

    public function forShow(Client $client): array
    {
        return [
            ...$this->forEdit($client),
            'source_label'    => $client->source?->label(),
            'status_label'    => $client->status->label(),
            'status_color'    => $client->status->color(),
            'created_at'      => $client->created_at->format('d M Y'),
            'date_of_birth_formatted' => $client->date_of_birth?->format('d M Y'),
        ];
    }

    public function create(array $data): Client
    {
        $mediaKeys  = ['avatar', 'new_kyc_documents', 'remove_kyc_documents'];
        $media      = Arr::only($data, $mediaKeys);
        $attributes = Arr::except($data, $mediaKeys);

        if (empty($attributes['password'])) {
            $attributes['password'] = Str::random(12);
        }

        $client = Client::create($attributes);
        $this->attachMedia($client, $media);

        return $client;
    }

    public function update(Client $client, array $data): Client
    {
        $mediaKeys  = ['avatar', 'remove_avatar', 'new_kyc_documents', 'remove_kyc_documents'];
        $media      = Arr::only($data, $mediaKeys);
        $attributes = Arr::except($data, $mediaKeys);

        if (empty($attributes['password'])) {
            unset($attributes['password']);
        }

        $client->update($attributes);
        $this->attachMedia($client, $media);

        return $client->fresh();
    }

    public function delete(Client $client): string
    {
        $name = $client->name;
        $client->delete();
        return $name;
    }

    private function attachMedia(Client $client, array $data): void
    {
        if (!empty($data['avatar'])) {
            $client->clearMediaCollection('avatar');
            $client->addMedia($data['avatar'])->toMediaCollection('avatar');
        } elseif ($data['remove_avatar'] ?? false) {
            $client->clearMediaCollection('avatar');
        }

        foreach ($data['new_kyc_documents'] ?? [] as $file) {
            $client->addMedia($file)->toMediaCollection('kyc_documents');
        }
        foreach ($data['remove_kyc_documents'] ?? [] as $mediaId) {
            $client->deleteMedia((int) $mediaId);
        }
    }
}
