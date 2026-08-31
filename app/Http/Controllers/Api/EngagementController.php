<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EngagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EngagementController extends Controller
{
    public function __construct(private EngagementService $engagement)
    {
    }

    public function show(Request $request, string $type, string $key): JsonResponse
    {
        return response()->json(
            $this->engagement->summary($type, $key, $this->guestId($request))
        );
    }

    public function view(Request $request, string $type, string $key): JsonResponse
    {
        $summary = $this->engagement->recordView($type, $key);
        $summary['my_reaction'] = $this->engagement->summary($type, $key, $this->guestId($request))['my_reaction'];

        return response()->json($summary);
    }

    public function react(Request $request, string $type, string $key): JsonResponse
    {
        $guestId = $this->guestId($request, required: true);
        $data = $request->validate([
            'reaction' => 'required|in:like,dislike',
        ]);

        return response()->json(
            $this->engagement->react($type, $key, $guestId, $data['reaction'])
        );
    }

    public function comments(string $type, string $key): JsonResponse
    {
        return response()->json($this->engagement->comments($type, $key));
    }

    public function storeComment(Request $request, string $type, string $key): JsonResponse
    {
        $data = $request->validate([
            'author_name' => 'required|string|max:40',
            'body' => 'required|string|max:500',
        ]);

        $comment = $this->engagement->addComment(
            $type,
            $key,
            $data['author_name'],
            $data['body'],
            $this->guestId($request)
        );

        return response()->json($comment, 201);
    }

    public function history(Request $request): JsonResponse
    {
        $guestId = $this->guestId($request, required: true);
        $name = trim((string) $request->query('name', ''));

        return response()->json(
            $this->engagement->history($guestId, $name !== '' ? $name : null)
        );
    }

    private function guestId(Request $request, bool $required = false): ?string
    {
        $id = trim((string) $request->header('X-Guest-Id', ''));
        if ($id === '') {
            $id = trim((string) $request->input('guest_id', ''));
        }

        if ($id !== '' && preg_match('/^[A-Za-z0-9_-]{8,64}$/', $id)) {
            return $id;
        }

        if ($required) {
            abort(422, 'ID pengunjung tidak valid. Muat ulang halaman lalu coba lagi.');
        }

        return null;
    }
}
