<?php

declare(strict_types=1);

namespace TacraftSociality\ServerOAuth\Api;

use Illuminate\Support\Arr;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TacraftSociality\ServerOAuth\Models\Scope;
use TacraftSociality\ServerOAuth\Permission;
use TacraftSociality\ServerOAuth\Support\JsonApi;
use TacraftSociality\ServerOAuth\Support\Serializer;

class ScopeController extends AbstractController
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return match ((string) $request->getAttribute('routeName')) {
            'oauth.scopes.list' => $this->index($request),
            'oauth.scopes.create' => $this->store($request),
            'oauth.scopes.update' => $this->update($request),
            'oauth.scopes.delete' => $this->destroy($request),
            default => JsonApi::error(404, 'Not found'),
        };
    }

    protected function index(ServerRequestInterface $request): ResponseInterface
    {
        $actor = $this->actor($request);
        $actor->assertRegistered();

        $full = $actor->isAdmin();

        if (! $full && ! $actor->hasPermission(Permission::USE_OAUTH)) {
            return JsonApi::collection('oauth-scopes', []);
        }

        $items = [];

        foreach (Scope::query()->orderBy('id')->get() as $scope) {
            $items[$scope->id] = Serializer::scope($scope, $full);
        }

        return JsonApi::collection('oauth-scopes', $items);
    }

    protected function store(ServerRequestInterface $request): ResponseInterface
    {
        $this->actor($request)->assertAdmin();

        $attributes = $this->attributes($request);
        $scopeName = trim((string) Arr::get($attributes, 'scope', ''));

        if ($scopeName === '') {
            return JsonApi::error(422, 'scope is required');
        }

        $scope = new Scope();
        $scope->scope = $scopeName;
        $scope->resource_path = (string) Arr::get($attributes, 'resource_path', '/api/');
        $scope->method = (string) Arr::get($attributes, 'method', 'GET');
        $scope->visible_fields = Arr::get($attributes, 'visible_fields');
        $scope->is_default = (bool) Arr::get($attributes, 'is_default', false);
        $scope->scope_name = Arr::get($attributes, 'scope_name');
        $scope->scope_icon = Arr::get($attributes, 'scope_icon');
        $scope->scope_desc = Arr::get($attributes, 'scope_desc');
        $scope->save();

        return JsonApi::item('oauth-scopes', $scope->id, Serializer::scope($scope), 201);
    }

    protected function update(ServerRequestInterface $request): ResponseInterface
    {
        $this->actor($request)->assertAdmin();

        $scope = Scope::query()->find($this->routeParam($request, 'id'));

        if ($scope === null) {
            return JsonApi::error(404, 'Scope not found');
        }

        $attributes = $this->attributes($request);

        foreach (['scope', 'resource_path', 'method', 'visible_fields', 'scope_name', 'scope_icon', 'scope_desc'] as $field) {
            $value = Arr::get($attributes, $field);

            if ($value !== null) {
                $scope->{$field} = $value;
            }
        }

        if (Arr::has($attributes, 'visible_fields')) {
            $scope->visible_fields = Arr::get($attributes, 'visible_fields');
        }

        if (Arr::has($attributes, 'is_default')) {
            $scope->is_default = (bool) Arr::get($attributes, 'is_default');
        }

        $scope->save();

        return JsonApi::item('oauth-scopes', $scope->id, Serializer::scope($scope));
    }

    protected function destroy(ServerRequestInterface $request): ResponseInterface
    {
        $this->actor($request)->assertAdmin();

        $scope = Scope::query()->find($this->routeParam($request, 'id'));

        if ($scope !== null) {
            $scope->delete();
        }

        return JsonApi::noContent();
    }
}
