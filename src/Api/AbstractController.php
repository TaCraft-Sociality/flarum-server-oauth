<?php

declare(strict_types=1);

namespace TacraftSociality\ServerOAuth\Api;

use Flarum\Http\RequestUtil;
use Flarum\User\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

abstract class AbstractController implements RequestHandlerInterface
{
    protected function actor(ServerRequestInterface $request): User
    {
        return RequestUtil::getActor($request);
    }

    protected function attributes(ServerRequestInterface $request): array
    {
        $body = $request->getParsedBody();

        if (! is_array($body)) {
            return [];
        }

        $attributes = $body['data']['attributes'] ?? [];

        return is_array($attributes) ? $attributes : [];
    }

    protected function routeParam(ServerRequestInterface $request, string $name): ?string
    {
        $value = $request->getQueryParams()[$name] ?? null;

        return $value === null ? null : (string) $value;
    }
}
