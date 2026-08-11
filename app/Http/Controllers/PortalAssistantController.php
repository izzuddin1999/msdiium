<?php

namespace App\Http\Controllers;

use App\Services\PortalAssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class PortalAssistantController extends Controller
{
    public function ask(Request $request, PortalAssistantService $assistant): JsonResponse
    {
        $viewer = $request->user();

        abort_unless($viewer && ! $viewer->canManagePolicies(), 403);

        $data = $request->validate([
            'question' => ['required', 'string', 'min:2', 'max:1000'],
            'history' => ['sometimes', 'array', 'max:6'],
            'history.*.role' => ['required_with:history', 'in:user,assistant'],
            'history.*.text' => ['required_with:history', 'string', 'max:1500'],
        ]);

        try {
            return response()->json($assistant->answer(
                $viewer,
                trim($data['question']),
                collect($data['history'] ?? [])
            ));
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'The portal assistant is temporarily unavailable. Please try again.',
            ], 502);
        }
    }
}
