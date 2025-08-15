<?php

namespace App\Http\Controllers\API;

use App\Models\Comment;
use App\Traits\GlobalTraits;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{

    use GlobalTraits;

    public function Comments()
    {
        $comment = Comment::all();
        if ($comment) {
            return $this->SendResponse($comment, 'success', 200);
        }
        return $this->SendResponse(null, 'success', 400);
    }

    public function comment($id)
    {
        $comment = Comment::find($id);
        if ($comment) {
            return $this->SendResponse($comment, 'success', 200);
        }
        return $this->SendResponse(null, 'success', 400);
    }
    public function store(Request $request)
    {
        $user_id = Auth::user()->id;
        $post_id = $request->post_id;

        $comment = new Comment;
        $comment->comment = $request->comment;
        $comment->rating = json_decode($request->rating);
        $comment->user_id = $user_id;
        $comment->post_id = $post_id;
        $comment->save();
        if ($comment) {
            $comments = Comment::with('user')->where('post_id', $post_id)->get();
            $avgRating = Comment::where('post_id', $post_id)->avg('rating');
            $comment->comments =  $comments;
            $comment->avg_rating = json_decode($avgRating);
            return $this->SendResponse($comment, 'success', 200);
        }
        return $this->SendResponse(null, 'Error, Not Found any post', 400);
    }

    public function update(Request $request, $id)
    {
        $user_id = Auth::user()->id;
        $post_id = $request->post_id;
        $comment =  Comment::find($id);
        $comment->comment = $request->comment;
        $comment->rating = json_decode($request->rating);
        $comment->user_id = $user_id;
        $comment->post_id = $post_id;
        $comment->update();
        if ($comment) {
            $comments = Comment::with('user')->where('post_id', $post_id)->get();
            $avgRating = Comment::where('post_id', $post_id)->avg('rating');
            $comment->comments =  $comments;
            $comment->avg_rating = json_decode($avgRating);
            return $this->SendResponse($comment, 'success', 200);
        }
        return $this->SendResponse(null, 'Error, Not Found any post', 400);
    }

    public function destroy(Request $request, $id)
    {
        $comment =  Comment::find($id);
        if ($comment) {
            $comment->delete();
            return $this->SendResponse($comment, 'success delete comments', 200);
        }
        return $this->SendResponse(null, 'Error, Not Found any comments', 400);
    }
}
