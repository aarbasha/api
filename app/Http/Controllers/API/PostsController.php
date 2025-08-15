<?php

namespace App\Http\Controllers\API;

use App\Models\Post;
use App\Models\Image;
use App\Models\Comment;
use App\Models\Categorie;
use App\Traits\GlobalTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostsController extends Controller
{
    use GlobalTraits;

    public function Posts()
    {
        $posts = Post::with('images', 'Categories')
            ->orderBy('created_at', 'desc')->paginate(8);

        if ($posts->isEmpty()) {
            return $this->SendResponse([], 'لا يوجد اي منشورات حتى الآن ', 200);
        }

        $productIds = $posts->pluck('id')->toArray();

        $avgRatings = DB::table('comments')
            ->whereIn('post_id', $productIds)
            ->select('post_id', DB::raw('AVG(rating) as average_rating'))
            ->groupBy('post_id')
            ->pluck('average_rating', 'post_id');

        $ratingsCounts = DB::table('comments')
            ->whereIn('post_id', $productIds)
            ->select('post_id', DB::raw('COUNT(*) as ratings_count'))
            ->groupBy('post_id')
            ->pluck('ratings_count', 'post_id');

        $FavoriteCounts = DB::table('favorites_posts')
            ->whereIn('post_id', $productIds)
            ->select('post_id', DB::raw('COUNT(*) as favorite_count'))
            ->groupBy('post_id')
            ->pluck('favorite_count', 'post_id');

        $comments_count = DB::table('comments')
            ->whereIn('post_id', $productIds)
            ->select('post_id', DB::raw('COUNT(*) as comments_count'))
            ->groupBy('post_id')
            ->pluck('comments_count', 'post_id');

        $comments = DB::table('comments')
            ->whereIn('post_id', $productIds)
            ->get();

        foreach ($posts as $product) {
            $product->avg_rating = json_decode($avgRatings[$product->id]  ?? 0);
            $product->ratings_count = $ratingsCounts[$product->id] ?? 0;
            $product->favorite_count = $FavoriteCounts[$product->id] ?? 0;
            $product->comments_count = $comments_count[$product->id] ?? 0;
            $product->comments = $comments->where('post_id', $product->id);
        }

        return $this->SendResponse($posts, 'Success, All Products', 200);
    }

    public function Post($id)
    {
        $post = Post::find($id);

        if ($post) {
            // تحميل العلاقات
            $post->load('Categories', 'images');

            $comments = Comment::with('user')
                ->where('post_id', $id)
                ->get();

            $comments_count = $comments->count();

            $avgRating = Comment::where('post_id', $id)->avg('rating');
            $users = $post->favorites()->with('user')->get()->pluck("user");
            $usersCount = $post->favorites()->with('user')->count();

            $post->comments = $comments;
            $post->avg_rating = json_decode($avgRating);
            $post->favorites = $users;
            $post->UserCountFavorites = $usersCount;
            $post->comments_count = $comments_count;

            return $this->SendResponse($post, 'success this all posts ', 200);
        }

        return $this->SendResponse(null, 'Error, Not Found any post', 400);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|Unique:posts',
            'cover' => 'required',
            'url' => 'url',
            'info' => 'required|string',
            'categorie_id' => 'required|exists:categories,id',
        ]);
        if ($validator->fails()) {
            return $this->SendResponse(null, $validator->errors(), 401);
        }

        do {
            $randomNumber  = mt_rand(100000, 999999);
        } while (Post::where("number", $randomNumber)->exists());

        if ($request->hasFile("cover")) {
            $file = $request->file("cover");
            $imageName = time() . '_' . $file->getClientOriginalName();
            $post = new Post();
            $post->title = $request->title;
            $post->url = $request->url;
            $post->info = $request->info;
            $post->number = $randomNumber;
            $post->categorie_id =  $request->categorie_id; // الفه
            $post->cover = $imageName;
            $file->move(public_path("cover/"), $imageName);
            $post->save();

            if ($request->hasFile("images")) {
                $files = $request->file("images");
                foreach ($files as $imagefile) {
                    $imageName = time() . '_' . $imagefile->getClientOriginalName();
                    $image = new Image;
                    $image->url = $imageName;
                    $image->post_id = $post->id;
                    $image->save();
                    $imagefile->move(public_path('images/'), $imageName);
                }
            }
            if ($post) {
                return $this->SendResponse($post, 'success', 200);
            }
        }
        return $this->SendResponse(null, 'Error', 401);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'url' => 'url',
            'info' => 'required|string',
            'categorie_id' => 'required|exists:categories,id',
        ]);
        if ($validator->fails()) {
            return $this->SendResponse(null, $validator->errors(), 401);
        }

        $post =  Post::with("images")->find($id);
        $post->title = $request->title;
        $post->url = $request->url;
        $post->info = $request->info;
        $post->categorie_id =  $request->categorie_id;
        if ($request->hasFile("cover")) {


            // حذف الصورة القديمة
            $coverPath = public_path('cover/' . $post->cover);
            if (file_exists($coverPath)) {
                unlink($coverPath);
                //echo 'delete cover file';
            }

            //اضافه الصوره الجديده
            $file = $request->file("cover");
            $imageName = time() . '_' . $file->getClientOriginalName();
            $post->cover = $imageName;
            $file->move(public_path("cover/"), $imageName);
        }
        $post->update();
        if ($request->hasFile("images")) {

            // حذف الصورة القديمة
            foreach ($post->images as $file) {
                $filePath = public_path('images/' . $file->url);
                if (file_exists($filePath)) {
                    unlink($filePath);
                    //echo 'delete image file';
                }
            }

            //اضافه الصوره الجديده
            $files = $request->file("images");
            foreach ($files as $imagefile) {
                $imageName = time() . '_' . $imagefile->getClientOriginalName();
                $image = new Image;
                $image->url = $imageName;
                $image->post_id = $post->id;
                $image->save();
                $imagefile->move(public_path('images/'), $imageName);
            }
        }
        if ($post) {
            return $this->SendResponse($post, 'success', 200);
        }
        return $this->SendResponse(null, 'Error', 401);
    }

    public function GetPostbyCategories($id)
    {
        $category = Categorie::find($id);
        if ($category) {
            $posts = $category->posts()->with('images', 'Categories')->orderBy('created_at', 'desc')->paginate(8);
        } else {
            return $this->SendResponse([], 'Error, No post Found', 400);
        }
        if ($posts->isEmpty()) {
            return $this->SendResponse([], 'Error, No post Found', 400);
        }

        $postIds = $posts->pluck('id')->toArray();

        $avgRatings = DB::table('comments')
            ->whereIn('post_id', $postIds)
            ->select('post_id', DB::raw('AVG(rating) as average_rating'))
            ->groupBy('post_id')
            ->pluck('average_rating', 'post_id');

        $ratingsCounts = DB::table('comments')
            ->whereIn('post_id', $postIds)
            ->select('post_id', DB::raw('COUNT(*) as ratings_count'))
            ->groupBy('post_id')
            ->pluck('ratings_count', 'post_id');

        $comments = DB::table('comments')
            ->whereIn('post_id', $postIds)
            ->get();
        foreach ($posts as $product) {
            $product->avg_rating = $avgRatings[$product->id] ?? 0;
            $product->ratings_count = $ratingsCounts[$product->id] ?? 0;
            $product->comments = $comments->where('post_id', $product->id);
        }

        return $this->SendResponse($posts, "success", 200);
    }


    public function destroy($id)
    {
        $post = Post::with('images')->find($id);
        if ($post) {
            $coverPath = public_path('cover/' . $post->cover);
            if (file_exists($coverPath)) {
                unlink($coverPath);
                //echo 'delete cover file';
            }

            foreach ($post->images as $file) {
                $filePath = public_path('images/' . $file->url);
                if (file_exists($filePath)) {
                    unlink($filePath);
                    //echo 'delete image file';
                }
            }

            $post->delete();
            return $this->SendResponse($post, 'success delete file', 200);
        }
    }



    public function TopRatingPost()
    {
        $products = Post::with('Tags', 'Sizes', 'Colors', 'images', 'Categories', 'brands')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        if ($products->isEmpty()) {
            return $this->SendResponse([], 'Error, No Products Found', 400);
        }

        $productIds = $products->pluck('id')->toArray();

        $avgRatings = DB::table('comments')
            ->whereIn('product_id', $productIds)
            ->select('product_id', DB::raw('AVG(rating) as average_rating'))
            ->groupBy('product_id')
            ->pluck('average_rating', 'product_id');

        $ratingsCounts = DB::table('comments')
            ->whereIn('product_id', $productIds)
            ->select('product_id', DB::raw('COUNT(*) as ratings_count'))
            ->groupBy('product_id')
            ->pluck('ratings_count', 'product_id');

        $FavoriteCounts = DB::table('favorites_products')
            ->whereIn('product_id', $productIds)
            ->select('product_id', DB::raw('COUNT(*) as favorite_count'))
            ->groupBy('product_id')
            ->pluck('favorite_count', 'product_id');

        $comments = DB::table('comments')
            ->whereIn('product_id', $productIds)
            ->get();

        // Get the top 10 products by average rating
        $topProducts = $products->sortByDesc(function ($product) use ($avgRatings) {
            return $avgRatings[$product->id] ?? 0;
        })->take(10);

        $topProductsArray = [];

        foreach ($topProducts as $product) {
            $topProductsArray[] = [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'avg_rating' => json_decode($avgRatings[$product->id] ?? 0),
                'ratings_count' => $ratingsCounts[$product->id] ?? 0,
                'favorite_count' => $FavoriteCounts[$product->id] ?? 0,
                'comments' => $comments->where('product_id', $product->id)->toArray(),
                'tags' => $product->Tags->toArray(),
                'sizes' => $product->Sizes->toArray(),
                'colors' => $product->Colors->toArray(),
                'images' => $product->images->toArray(),
                'categories' => $product->Categories->toArray(),
                'brand' => $product->brands->toArray(),
            ];
        }

        return $this->SendResponse($topProductsArray, 'Success, Top 10 Products', 200);
    }
}
