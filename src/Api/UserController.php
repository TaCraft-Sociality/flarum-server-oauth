<?php

declare(strict_types=1);

namespace TacraftSociality\ServerOAuth\Api;

use Flarum\Http\RequestUtil;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TacraftSociality\ServerOAuth\Support\Date;

class UserController implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $actor = RequestUtil::getActor($request);

        if ($actor->isGuest()) {
            return new JsonResponse([
                'error' => 'invalid_token',
                'error_description' => 'A valid access token is required',
            ], 401);
        }

        return new JsonResponse([
            'id' => (int) $actor->id,
            'username' => $actor->username,
            'nickname' => $actor->nickname,
            'avatar_url' => $actor->avatar_url,
            'email' => $actor->email,
            'is_email_confirmed' => (bool) $actor->is_email_confirmed,
            'joined_at' => Date::format($actor->joined_at),
            'last_seen_at' => Date::format($actor->last_seen_at),
            'discussion_count' => (int) $actor->discussion_count,
            'comment_count' => (int) $actor->comment_count,
        ]);
    }
}
