<?php

declare(strict_types=1);

namespace TacraftSociality\ServerOAuth\Api;

use Illuminate\Support\Arr;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TacraftSociality\ServerOAuth\Models\Client;
use TacraftSociality\ServerOAuth\Permission;
use TacraftSociality\ServerOAuth\Support\JsonApi;
use TacraftSociality\ServerOAuth\Support\Serializer;

class ClientController extends AbstractController
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return match ((string) $request->getAttribute('routeName')) {
            'oauth.clients.list' => $this->index($request),
            'oauth.clients.show' => $this->show($request),
            'oauth.clients.create' => $this->store($request),
            'oauth.clients.update' => $this->update($request),
            'oauth.clients.delete' => $this->destroy($request),
            default => JsonApi::error(404, 'Not found'),
        };
    }

    protected function index(ServerRequestInterface $request): ResponseInterface
    {
        $this->actor($request)->assertAdmin();

        $items = [];

        foreach (Client::query()->orderBy('id')->get() as $client) {
            $items[$client->id] = Serializer::client($client);
        }

        return JsonApi::collection('oauth-clients', $items);
    }

    protected function show(ServerRequestInterface $request): ResponseInterface
    {
        $actor = $this->actor($request);
        $actor->assertRegistered();

        if (! $actor->hasPermission(Permission::USE_OAUTH)) {
            return JsonApi::error(403, 'You do not have permission to use OAuth');
        }

        $client = Client::query()
            ->where('client_id', $this->routeParam($request, 'client_id'))
            ->first();

        if ($client === null) {
            return JsonApi::error(404, 'Client not found');
        }

        return JsonApi::item('oauth-clients', $client->id, Serializer::client($client, false));
    }

    protected function store(ServerRequestInterface $request): ResponseInterface
    {
        $actor = $this->actor($request);
        $actor->assertAdmin();

        $attributes = $this->attributes($request);
        $clientId = trim((string) Arr::get($attributes, 'client_id', ''));

        if ($clientId === '') {
            return JsonApi::error(422, 'client_id is required');
        }

        $client = Client::query()->where('client_id', $clientId)->first();

        if ($client !== null) {
            return JsonApi::item('oauth-clients', $client->id, Serializer::client($client));
        }

        $client = new Client();
        $client->client_id = $clientId;
        $client->client_secret = Arr::get($attributes, 'client_secret');
        $client->redirect_uri = Arr::get($attributes, 'redirect_uri');
        $client->grant_types = Arr::get($attributes, 'grant_types');
        $client->scope = Arr::get($attributes, 'scope');
        $client->user_id = (string) $actor->id;
        $client->client_name = Arr::get($attributes, 'client_name');
        $client->client_icon = Arr::get($attributes, 'client_icon');
        $client->client_desc = Arr::get($attributes, 'client_desc');
        $client->client_home = Arr::get($attributes, 'client_home');
        $client->save();

        return JsonApi::item('oauth-clients', $client->id, Serializer::client($client), 201);
    }

    protected function update(ServerRequestInterface $request): ResponseInterface
    {
        $this->actor($request)->assertAdmin();

        $client = Client::query()->find($this->routeParam($request, 'id'));

        if ($client === null) {
            return JsonApi::error(404, 'Client not found');
        }

        $attributes = $this->attributes($request);

        foreach (['client_id', 'client_secret', 'redirect_uri', 'grant_types', 'scope', 'client_name', 'client_icon', 'client_desc', 'client_home'] as $field) {
            $value = Arr::get($attributes, $field);

            if ($value === null) {
                continue;
            }

            if (in_array($field, ['grant_types', 'scope'], true) && $value === '') {
                $value = null;
            }

            $client->{$field} = $value;
        }

        $client->save();

        return JsonApi::item('oauth-clients', $client->id, Serializer::client($client));
    }

    protected function destroy(ServerRequestInterface $request): ResponseInterface
    {
        $this->actor($request)->assertAdmin();

        $client = Client::query()->find($this->routeParam($request, 'id'));

        if ($client !== null) {
            $client->delete();
        }

        return JsonApi::noContent();
    }
}
