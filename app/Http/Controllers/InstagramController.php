<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class InstagramController extends Controller
{
    public function media(Request $request)
    {
        $limit = (int) ($request->query('limit', 9));
        $token = env('IG_ACCESS_TOKEN');
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Instagram access token not configured',
                'data' => null,
                'errors' => (object)[],
            ], 500);
        }

        $response = Http::get('https://graph.instagram.com/me/media', [
            'fields' => 'id,media_type,media_url,thumbnail_url,permalink,caption',
            'access_token' => $token,
            'limit' => $limit,
        ]);

        if ($response->failed()) {
            return response()->json([
                'success' => false,
                'message' => 'Instagram API error',
                'data' => null,
                'errors' => [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ],
            ], $response->status());
        }

        $data = $response->json('data') ?: [];

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => $data,
            'errors' => (object)[],
        ]);
    }
}
