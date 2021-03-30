<?php

namespace App\Observers;

use App\Models\Comment;
use Illuminate\Support\Facades\Auth;

class CommentObserver
{
    /**
     * Handle the "saving" event.
     *
     * @param  Comment $comment
     * @return void
     */
    public function saving(Comment $comment) {
        if (Auth::check()) {
            $comment->user_id = Auth::id();
        }
    }
}
