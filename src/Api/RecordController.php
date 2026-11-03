<?php

declare(strict_types=1);

namespace TacraftSociality\ServerOAuth\Api;

use Flarum\Settings\SettingsRepositoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TacraftSociality\ServerOAuth\Models\Record;
use TacraftSociality\ServerOAuth\Settings;
use TacraftSociality\ServerOAuth\Support\JsonApi;
use TacraftSociality\ServerOAuth\Support\Serializer;

class RecordController extends AbstractController
{
    public function __construct(
        protected SettingsRepositoryInterface $settings
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return match ((string) $request->getAttribute('routeName')) {
            'oauth.records.list' => $this->index($request),
            'oauth.records.delete.all' => $this->deleteAll($request),
            'oauth.records.delete.user' => $this->deleteUser($request),
            'oauth.records.delete' => $this->destroy($request),
            default => JsonApi::error(404, 'Not found'),
        };
    }

    protected function index(ServerRequestInterface $request): ResponseInterface
    {
        $actor = $this->actor($request);
        $actor->assertRegistered();

        $page = max(0, (int) ($request->getQueryParams()['page'] ?? 0));

        $records = Record::query()
            ->with('client')
            ->where('user_id', (string) $actor->id)
            ->orderByDesc('authorized_at')
            ->skip($page * 10)
            ->take(10)
            ->get();

        $items = [];

        foreach ($records as $record) {
            $items[$record->id] = Serializer::record($record);
        }

        return JsonApi::collection('oauth-records', $items);
    }

    protected function deleteAll(ServerRequestInterface $request): ResponseInterface
    {
        $this->actor($request)->assertAdmin();

        Record::query()->delete();

        return JsonApi::noContent();
    }

    protected function deleteUser(ServerRequestInterface $request): ResponseInterface
    {
        $actor = $this->actor($request);
        $actor->assertRegistered();

        if (! $this->allowDelete()) {
            return JsonApi::error(403, 'Deleting authorization records is disabled');
        }

        Record::query()->where('user_id', (string) $actor->id)->delete();

        return JsonApi::noContent();
    }

    protected function destroy(ServerRequestInterface $request): ResponseInterface
    {
        $actor = $this->actor($request);
        $actor->assertRegistered();

        if (! $this->allowDelete()) {
            return JsonApi::error(403, 'Deleting authorization records is disabled');
        }

        $record = Record::query()->find($this->routeParam($request, 'id'));

        if ($record === null) {
            return JsonApi::noContent();
        }

        if (! $actor->isAdmin() && (string) $record->user_id !== (string) $actor->id) {
            return JsonApi::error(403, 'You cannot delete this record');
        }

        $record->delete();

        return JsonApi::noContent();
    }

    protected function allowDelete(): bool
    {
        return (bool) $this->settings->get(Settings::ALLOW_DELETE_RECORDS, '0');
    }
}
