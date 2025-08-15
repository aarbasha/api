<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\PostsController;
use App\Http\Controllers\API\CommentController;
use App\Http\Controllers\API\CategorieController;




Route::controller(CategorieController::class)->group(function () {
    Route::prefix('categories')->group(function () {
        Route::get('/categories', 'categories');
        Route::get('/sub', 'subCategory');
        Route::get('/all', 'index');
        Route::get('/{id}', 'categorie');
        Route::get('/sub/{id}', 'subCategorie');
    });
});

// Route::controller(PostsController::class)->group(function () {
//     Route::prefix('posts')->group(function () {
//         Route::get('/all', 'Products');
//         Route::get('/product/{id}', 'Product');
//         Route::get('/productsByTags/{id}', 'GetProductbyTag');
//         Route::get('/productsByCategories/{id}', 'GetProductbyCategories');
//         Route::get('/productsByBrands/{id}', 'GetProductbyBrands');
//         Route::get('/{id}', 'tag');
//         Route::get('/top/rating', 'TopRatingProduct');
//     });
// });


Route::controller(PostsController::class)->group(function () {
    Route::prefix('posts')->group(function () {
        Route::get('/all', 'Posts');
        Route::get('/{id}', 'Post');
        Route::get('/postByCategories/{id}', 'GetPostbyCategories');
        // Route::post("/delete_Image/{id}", "Delete_Imgage_post");
    });
});

Route::controller(CommentController::class)->group(function () {
    Route::prefix('comments')->group(function () {
        Route::get('/all', 'comments');
        Route::get('/{id}', 'comment');
    });
});
