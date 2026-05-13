<?php

namespace App\Http\Controllers;

use App\Http\Requests\WebhookRequest;
use App\Jobs\ProcessOmiseWebhook;
use Illuminate\Http\JsonResponse;

class WebhookController extends Controller
{
    public function handle(WebhookRequest $request): JsonResponse
    {
        ProcessOmiseWebhook::dispatch($request->all());

        return response()->json(['status' => 'ok']);
    }
}
