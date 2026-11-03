<?php

declare(strict_types=1);

namespace TacraftSociality\ServerOAuth\OAuth;

use Laminas\Diactoros\Response;
use OAuth2\Response as OAuthResponse;
use Psr\Http\Message\ResponseInterface;

class ResponseFactory
{
    public static function fromOAuth(OAuthResponse $response): ResponseInterface
    {
        $body = $response->getResponseBody();
        $psr = new Response('php://memory', $response->getStatusCode());

        if ($body !== null && $body !== '') {
            $psr->getBody()->write($body);
        }

        foreach ($response->getHttpHeaders() as $name => $value) {
            $psr = $psr->withHeader($name, $value);
        }

        if ($body !== null && $body !== '' && ! $psr->hasHeader('Content-Type')) {
            $psr = $psr->withHeader('Content-Type', 'application/json');
        }

        return $psr;
    }
}
