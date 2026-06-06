<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class MerchantUnauthorizedResponse
{
    public static function make(?Request $request = null): Response
    {
        $request ??= request();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('merchant.unauthorized.message'),
            ], Response::HTTP_FORBIDDEN);
        }

        return response()->view(
            'merchant.unauthorized',
            [],
            Response::HTTP_FORBIDDEN,
        );
    }
}
