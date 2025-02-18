<?php

declare(strict_types=1);

namespace App\Community\Controllers\Api;

use App\Community\Actions\FetchDynamicShortcodeContentAction;
use App\Community\Requests\PreviewForumPostRequest;
use App\Community\Requests\UpdateForumTopicCommentRequest;
use App\Http\Controller;
use App\Models\ForumTopicComment;
use App\Support\Shortcode\Shortcode;
use Illuminate\Http\JsonResponse;

class ForumTopicCommentApiController extends Controller
{
    public function store(): void
    {
    }

    public function update(
        UpdateForumTopicCommentRequest $request,
        ForumTopicComment $comment
    ): JsonResponse {
        $this->authorize('update', $comment);

        // Take any RA links and convert them to relevant shortcodes.
        // eg: "https://retroachievements.org/game/1" --> "[game=1]"
        $newPayload = normalize_shortcodes($request->input('body'));

        // Convert [user=$user->username] to [user=$user->id].
        $newPayload = Shortcode::convertUserShortcodesToUseIds($newPayload);

        $comment->body = $newPayload;
        $comment->save();

        return response()->json(['success' => true]);
    }

    public function destroy(): void
    {
    }

    public function preview(
        PreviewForumPostRequest $request,
        FetchDynamicShortcodeContentAction $action
    ): JsonResponse {
        $entities = $action->execute(
            usernames: $request->input('usernames'),
            ticketIds: $request->input('ticketIds'),
            achievementIds: $request->input('achievementIds'),
            gameIds: $request->input('gameIds'),
            hubIds: $request->input('hubIds'),
        );

        return response()->json($entities);
    }
}
