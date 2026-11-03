<?php

declare(strict_types=1);

namespace TacraftSociality\ServerOAuth\Middleware;

use Illuminate\Support\Arr;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ScopeFields implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        $scopes = $request->getAttribute('oauth.scopes');

        if (! is_array($scopes) || $scopes === []) {
            return $response;
        }

        $fields = [];

        foreach ($scopes as $scope) {
            foreach (explode(',', (string) $scope->visible_fields) as $field) {
                $field = trim($field);

                if ($field !== '') {
                    $fields[] = $field;
                }
            }
        }

        $fields = array_values(array_unique($fields));

        if ($fields === []) {
            return $response;
        }

        $origin = json_decode((string) $response->getBody(), true);

        if (! is_array($origin)) {
            return $response;
        }

        $data = [];

        foreach ($fields as $field) {
            if (Arr::has($origin, $field)) {
                Arr::set($data, $field, Arr::get($origin, $field));
            }
        }

        return new JsonResponse($data, $response->getStatusCode());
    }
}
