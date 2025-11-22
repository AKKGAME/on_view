<?php

namespace App\Livewire;

use App\Models\Comment;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class EpisodeComments extends Component
{
    use WithPagination;

    public $episodeId;
    public $body;
    public $is_spoiler = false;

    public function mount($episodeId)
    {
        $this->episodeId = $episodeId;
    }

    public function postComment()
    {
        $this->validate([
            'body' => 'required|min:3|max:1000',
        ]);

        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        Comment::create([
            'user_id' => $user->id,
            'episode_id' => $this->episodeId,
            'body' => $this->body,
            'is_spoiler' => $this->is_spoiler,
        ]);

        // Reward XP for engagement
        $user->increment('xp', 5);

        $this->reset(['body', 'is_spoiler']);

        $this->dispatch('notify', 
            type: 'success', 
            title: 'Comment Posted', 
            message: 'You earned +5 XP!'
        );
    }

    /**
     * Deletes a comment if the user owns it.
     * * @param int $commentId
     */
    public function deleteComment($commentId)
    {
        if (!Auth::check()) {
            return;
        }

        $comment = Comment::find($commentId);

        // Check if comment exists and if the current user is the owner
        if ($comment && $comment->user_id === Auth::id()) {
            $comment->delete();

            $this->dispatch('notify', 
                type: 'info', 
                title: 'Comment Deleted', 
                message: 'Your comment has been successfully removed.'
            );
        } else {
            // Optional: Notify if unauthorized action is attempted
            $this->dispatch('notify', 
                type: 'error', 
                title: 'Unauthorized', 
                message: 'You can only delete your own comments.'
            );
        }
    }

    public function render()
    {
        return view('livewire.episode-comments', [
            'comments' => Comment::where('episode_id', $this->episodeId)
                ->with('user')
                ->latest()
                ->paginate(10)
        ]);
    }
}