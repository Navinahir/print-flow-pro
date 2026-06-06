<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class AdminUnauthorizedResponse
{
    public static function make(?Request $request = null): Response
    {
        $request ??= request();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('admin.unauthorized.message'),
            ], Response::HTTP_FORBIDDEN);
        }

        return response()->view(
            'admin.unauthorized',
            [],
            Response::HTTP_FORBIDDEN,
        );
    }
}
