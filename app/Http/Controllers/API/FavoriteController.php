<?php

namespace App\Http\Controllers\API;

use App\Models\Post;
use App\Models\Favorite;
use Illuminate\Http\Request;
use App\Events\AddToFavorite;
use App\Events\RemoveFavorite;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function Favorites()
    {
        $user_id = Auth::user()->id;
        $Favorite = Favorite::where('user_id', $user_id)->first();
        if (!$Favorite) {
            return response()->json(['posts' => [], 'message' => 'قائمه المفضله فارغه']); //القائمه المفضله  فارغه
        } else {
            $posts = $Favorite->posts()->with('images', 'Categories')->orderBy('created_at', 'desc')->paginate(10);
            $post_idIds = $posts->pluck('id')->toArray();

            $avgRatings = DB::table('comments')
                ->whereIn('post_id', $post_idIds)
                ->select('post_id', DB::raw('AVG(rating) as average_rating'))
                ->groupBy('post_id')
                ->pluck('average_rating', 'post_id');

            $ratingsCounts = DB::table('comments')
                ->whereIn('post_id', $post_idIds)
                ->select('post_id', DB::raw('COUNT(*) as ratings_count'))
                ->groupBy('post_id')
                ->pluck('ratings_count', 'post_id');

            $FavoriteCounts = DB::table('favorites_posts')
                ->whereIn('post_id', $post_idIds)
                ->select('post_id', DB::raw('COUNT(*) as favorite_count'))
                ->groupBy('post_id')
                ->pluck('favorite_count', 'post_id');

            foreach ($posts as $product) {
                $product->avg_rating = $avgRatings[$product->id] ?? 0;
                $product->favorite_count = $FavoriteCounts[$product->id] ?? 0;
                $product->ratings_count = $ratingsCounts[$product->id] ?? 0;
            }
            // $totalPrice = $products->sum(function ($product) {
            //     return $product->price * $product->pivot->quantity;
            // });

            return response()->json(['posts' => $posts]);
        }
    }

    public function User_favorites($id)
    {
        $product = Post::find($id);
        $users = $product->favorites()->with('user')->get()->pluck("user");
        $usersCount = $product->favorites()->with('user')->get()->count();
        return response()->json(["users" => $users, "count" => $usersCount, 'id' => json_decode($id)]);
    }

    public function addToFavorites(Request $request)
    {
        $user_id = Auth::user()->id;
        $Favorite = Favorite::where('user_id', $user_id)->first();

        if (!$Favorite) {
            $Favorite = new Favorite;
            $Favorite->user_id = $user_id;
            $Favorite->save();
        }
        $post_id = $request->post_id;
        $existingPost = $Favorite->posts()->where('post_id', $post_id)->first();

        if ($existingPost) {
            return response()->json(['message' => 'المنشور موجود مسبقاً في قائمه المفضله']);
        } else {
            $Favorite->posts()->attach($post_id);
            $Favorite->save();
            event(new AddToFavorite($post_id));
            return response()->json(['message' => 'تمت إضافة المنشور إلى قائمه المفضله بنجاح', "post" => $post_id]);
        }
    }

    public function removeFromFavorites(Request $request)
    {

        $user_id = Auth::user()->id;
        $Favorite = Favorite::where('user_id', $user_id)->first();

        if ($Favorite) {
            //$product = $Favorite->products()->find($request->product_id);
            $Favorite->posts()->detach($request->post_id);
            event(new RemoveFavorite($request->post_id));
            return response()->json(['message' => 'تمت ازاله المنشور  من  المفضله بنجاح',  "post" => $request->post_id]);
        } else {
            return response()->json(['message' => 'tha post not found']);
        }
    }

    public function removeAllFavorites(Request $request)
    {

        $user_id = Auth::user()->id;
        $Favorite = Favorite::where('user_id', $user_id)->first();
        if ($Favorite) {
            $Favorite->posts()->sync([]);
            return response()->json(['message' => 'تم تفريغ  قائمه المفضله بنجاح']);
        } elseif (!$Favorite) {

            return response()->json(['message' => " قائمه المفضله فارغا تماما "]);
        }
    }
}
