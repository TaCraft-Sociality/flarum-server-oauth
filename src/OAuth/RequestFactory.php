<?php

declare(strict_types=1);

namespace TacraftSociality\ServerOAuth\OAuth;

use OAuth2\Request as OAuthRequest;
use Psr\Http\Message\ServerRequestInterface;

class RequestFactory
{
    public static function fromPsr(ServerRequestInterface $request): OAuthRequest
    {
        $uri = $request->getUri();

        $headers = [];

        foreach ($request->getHeaders() as $name => $values) {
            $headers[$name] = implode(', ', $values);
        }

        $server = [
            'REQUEST_METHOD' => $request->getMethod(),
            'REQUEST_URI' => (string) $uri,
            'QUERY_STRING' => $uri->getQuery(),
            'SERVER_NAME' => $uri->getHost(),
            'SERVER_PORT' => $uri->getPort() ?? ($uri->getScheme() === 'https' ? 443 : 80),
            'HTTPS' => $uri->getScheme() === 'https' ? 'on' : 'off',
            'REMOTE_ADDR' => (string) ($request->getServerParams()['REMOTE_ADDR'] ?? '127.0.0.1'),
            'CONTENT_TYPE' => $request->getHeaderLine('Content-Type'),
            'HTTP_AUTHORIZATION' => $request->getHeaderLine('Authorization'),
        ];

        return new OAuthRequest(
            $request->getQueryParams(),
            self::body($request),
            [],
            $request->getCookieParams(),
            [],
            $server,
            null,
            $headers
        );
    }

    protected static function body(ServerRequestInterface $request): array
    {
        $body = $request->getParsedBody();

        if (is_array($body) && $body !== []) {
            return $body;
        }

        $raw = (string) $request->getBody();

        if ($raw !== '' && str_contains($request->getHeaderLine('Content-Type'), 'application/x-www-form-urlencoded')) {
            parse_str($raw, $parsed);

            return is_array($parsed) ? $parsed : [];
        }

        return is_array($body) ? $body : [];
    }
}
