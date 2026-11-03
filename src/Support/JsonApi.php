<?php

declare(strict_types=1);

namespace TacraftSociality\ServerOAuth\Support;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;

final class JsonApi
{
    public static function item(string $type, string|int $id, array $attributes, int $status = 200): JsonResponse
    {
        return new JsonResponse([
            'data' => [
                'type' => $type,
                'id' => (string) $id,
                'attributes' => $attributes,
            ],
        ], $status);
    }

    public static function collection(string $type, iterable $items): JsonResponse
    {
        $data = [];

        foreach ($items as $id => $attributes) {
            $data[] = [
                'type' => $type,
                'id' => (string) $id,
                'attributes' => $attributes,
            ];
        }

        return new JsonResponse(['data' => $data]);
    }

    public static function error(int $status, string $detail): JsonResponse
    {
        return new JsonResponse([
            'errors' => [
                [
                    'status' => (string) $status,
                    'detail' => $detail,
                ],
            ],
        ], $status);
    }

    public static function noContent(): ResponseInterface
    {
        return new Response('php://memory', 204);
    }
}
