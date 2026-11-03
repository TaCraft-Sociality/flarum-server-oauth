<?php

declare(strict_types=1);

namespace TacraftSociality\ServerOAuth\Middleware;

use Flarum\Http\RequestUtil;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TacraftSociality\ServerOAuth\Models\Client;

/**
 * Refuses to show the consent screen to an account whose email is not
 * confirmed, sending the third-party site an OAuth error instead.
 */
class VerifiedEmailGate implements MiddlewareInterface
{
    private const LOCALE_KEY = 'tacraft-sociality-server-oauth.forum.authorize.email_unverified';

    private const FALLBACK_MESSAGE = '社区账号邮箱未验证，请先在社区完成邮箱验证后再登录。';

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (! preg_match('#/oauth/authorize(?:/fetch)?/?$#', $request->getUri()->getPath())) {
            return $handler->handle($request);
        }

        $actor = RequestUtil::getActor($request);

        // Guests get the normal page, which asks them to sign in first.
        if (! $actor->exists || $actor->is_email_confirmed) {
            return $handler->handle($request);
        }

        $body = $request->getParsedBody();
        $params = (is_array($body) ? $body : []) + $request->getQueryParams();

        $redirectUri = (string) ($params['redirect_uri'] ?? '');
        $clientId = (string) ($params['client_id'] ?? '');

        if ($redirectUri === '' || $clientId === '') {
            return $handler->handle($request);
        }

        // Only ever redirect to an address that the client registered, so a
        // crafted link cannot use this as an open redirect.
        try {
            $client = Client::query()->where('client_id', $clientId)->first();
        } catch (\Throwable $e) {
            return $handler->handle($request);
        }

        $registered = $client ? (preg_split('/\s+/', (string) $client->redirect_uri) ?: []) : [];

        if (! $client || ! in_array($redirectUri, $registered, true)) {
            return $handler->handle($request);
        }

        $query = array_filter([
            'error' => 'access_denied',
            'error_description' => $this->message(),
            'state' => $params['state'] ?? null,
        ], static fn ($value) => $value !== null && $value !== '');

        try {
            resolve('log')->info('[server-oauth] denied authorization for an unverified account', [
                'user_id' => $actor->id,
                'client_id' => $clientId,
            ]);
        } catch (\Throwable $e) {
            // Logging must never break the redirect.
        }

        return new RedirectResponse(
            $redirectUri.(str_contains($redirectUri, '?') ? '&' : '?').http_build_query($query)
        );
    }

    private function message(): string
    {
        try {
            $message = (string) resolve('translator')->trans(self::LOCALE_KEY);
        } catch (\Throwable $e) {
            return self::FALLBACK_MESSAGE;
        }

        return $message === '' || str_starts_with($message, 'tacraft-') ? self::FALLBACK_MESSAGE : $message;
    }
}
