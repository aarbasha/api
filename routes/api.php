<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ChatController;
use App\Http\Controllers\API\PostsController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\BlockController;
use App\Http\Controllers\Auth\UsersController;
use App\Http\Controllers\API\CommentController;
use App\Http\Controllers\API\FavoriteController;
use App\Http\Controllers\API\CategorieController;
use App\Http\Controllers\API\CallsAudioController;
use App\Http\Controllers\API\CallsVideoController;
use App\Http\Controllers\Auth\RoleAndPermissionController;
use App\Http\Controllers\Auth\TelegramController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\API\VisitorController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::post('login', [AuthController::class, 'login'])->name('login')->middleware('throttle:5,1');
Route::post('register', [AuthController::class, 'register']);
Route::get('test', [AuthController::class, 'test']);

Route::post('/visitors', [VisitorController::class, 'store']);


Route::middleware('web')->group(function () {
    Route::get('auth/{provider}', [SocialAuthController::class, 'redirectToProvider']);
    Route::get('auth/{provider}/callback', [SocialAuthController::class, 'handleProviderCallback']);
});

// Route::get('/telegram/login/callback/{userId}', [TelegramController::class, 'loginCallback'])->name('telegram.login.callback');
// Reset password
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
// verify code and create token if true
Route::post('/verify-code', [AuthController::class, 'verifyCode']);
// verfiy email
// Route::post('/verify-email', [AuthController::class, 'VerifyEmail']);
// Route::middleware('auth:sanctum')->post('/check-token', function (Request $request) {
//     if (Auth::check()) {
//         return response()->json(['message' => 'Token is work'], 200);
//     } else {
//         return response()->json(['message' => 'Token is invalid'], 400);
//     }
// });
// Route::controller(TelegramController::class)->group(function () {
//     Route::prefix('telegram')->group(function () {
//         Route::get('/login/callback', 'loginCallback')->name('telegram.login.callback');
//         Route::post('/handle-contact', 'handleContact')->name('telegram.handle.contact');

//         Route::get('/start-chat', function () {
//             return response()->json([
//                 'url' => 'https://t.me/anas3_bot' // استبدل بـ اسم البوت الخاص بك
//             ]);
//         });
//         Route::post('/webhook', 'handleMessage');
//     });
// });
Route::middleware('auth:sanctum')->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('profile', [AuthController::class, 'profile']);
        Route::post('upProfile', [AuthController::class, 'updateProfile']);
        Route::post('refresh-token', [AuthController::class, 'RefreshToken']);
        Route::post('setOnline', [AuthController::class, 'setOnline']);
        Route::get('online', [AuthController::class, 'UsersOnlineOffline']);
        Route::get('UsersCount', [AuthController::class, 'UsersCount']);
        Route::post('/logoutUser/{id}', 'logoutAnyUser');
        Route::post('RemoveProfileAvatar', [AuthController::class, 'RemoveProfileAvatar']);
        // update password
        Route::post('/update-password', [AuthController::class, 'UpdatePassword']);
        Route::post('/active-2fa-auth', [AuthController::class, 'Active2faAuth']);
        Route::post('/verify-code-2fa', [AuthController::class, 'verifyCodeFirst']);
        Route::post('/update-password-current', [AuthController::class, 'UpdatePasswordCurrent']);
        Route::post('/send-verification-code', [AuthController::class, 'sendVerificationCode']);
        Route::post('/verification-code-phone', [AuthController::class, 'verifyCodePhone']);
    });
    Route::controller(RoleAndPermissionController::class)->group(function () {
        Route::prefix('roles')->group(function () {
            Route::get('/all', 'Roles');
            Route::get('/{id}', 'Role');
            Route::post('/add', 'create');
            Route::post('/up/{id}', 'update');
            Route::delete('/{id}', 'delete');
            Route::post('/remove_role_user', 'RemoveRoleFromUser');
            Route::post('/add_users_to_role', 'giveRoleToUser');
            Route::post('/remove_all_users_from_role', 'RemoveAllUsersFormRole');
        });

        Route::prefix('permissons')->group(function () {
            Route::get('/all', 'Permissons');
            Route::post('/givePerToRole', 'givePerToRole');
            Route::post('/RemoveAllPerForRole', 'RemoveAllPerForRole');
        });
    });

    Route::controller(UsersController::class)->group(function () {
        Route::prefix('users')->group(function () {
            Route::get('/all_users', 'all_users');
            Route::get('/all', 'users');
            Route::get('/{id}', 'user');
            Route::post('/add', 'store');
            Route::post('/up/{id}', 'update');
            Route::delete('/{id}', 'destroy');
            Route::get('/online', "UsersOnlineOffline");
            Route::get('/roles/{id}', 'userTypeRoles');
            Route::post('/{id}', 'ToggleActive');
            Route::put('/{name}', 'searchUsers');
        });
    });
    Route::controller(BlockController::class)->group(function () {
        Route::prefix('block')->group(function () {
            Route::get('/my_block', 'usersMyBlock');
            Route::get('/block_me', 'usersBlockToMy');

            Route::post('/{id}', 'AddBlock');
            Route::post('/un/{id}', 'unBlock');
            Route::get('/users_block', 'Blocked_blocker');
        });
    });



    Route::controller(CategorieController::class)->group(function () {
        Route::prefix('categories')->group(function () {
            Route::get('/all', 'categories');
            Route::get('/sub', "subCategory");
            Route::get('/{id}', 'categorie');
            Route::get('/sub/{id}', 'subCategorie');
            Route::post('/add', 'storeSubCategories');
            Route::post('/up/{id}', 'update');
            Route::delete('/{id}', 'destroy');

            Route::post('/multe', 'multeDestroy');


            // Route::get('test', 'creteFoldersInPublic');
        });
    });


    Route::controller(PostsController::class)->group(function () {
        Route::prefix('posts')->group(function () {
            Route::get('/all', 'Posts');
            Route::get('/{id}', 'Post');
            Route::post('/add', 'store');
            Route::post('/up/{id}', 'update');
            Route::delete('/{id}', 'destroy');
            Route::post('/multe', 'multeDestroy');
            Route::get('/postByCategories/{id}', 'GetPostbyCategories');
            // Route::post("/delete_Image/{id}", "Delete_Imgage_post");
        });
    });

    Route::controller(CommentController::class)->group(function () {
        Route::prefix('comments')->group(function () {
            Route::get('/all', 'comments');
            Route::get('/{id}', 'comment');
            Route::post('/add', 'store');
            Route::post('/up/{id}', 'update');
            Route::delete('/{id}', 'destroy');
        });
    });


    Route::controller(FavoriteController::class)->group(function () {
        Route::prefix('favorite')->group(function () {
            Route::get('/all', 'Favorites');
            Route::post('/add', 'addToFavorites');
            Route::post('/remove', 'removeFromFavorites');
            Route::post('/empty', 'removeAllFavorites');
            Route::get('/favorites/{id}', 'User_favorites');
        });
    });



    Route::controller(ChatController::class)->group(function () {
        Route::prefix('chat')->group(function () {
            //create new order and pay and delete cart
            Route::get('', 'getAllMessages');
            // massge is reed
            Route::get('/old/{id}', 'OldMessage');
            // massge not reed
            Route::get('/new/{id}', 'NewMessage');
            //make to reed
            Route::put('/{id}', 'SetReedMessages');
            Route::get('/noreed', 'GetNoReedMassage');
            Route::post('/uplode_voice/{id}', 'sendMedia');
            Route::post('/{id}', 'sendMessage');
            Route::post('/typing/{id}', 'updateTypingStatus');
            Route::delete('/{id}', 'DeleteChatForm');
            Route::delete('/message/{id}', 'DeleteMessage');
            Route::post('/message/up/{id}', 'UpdateMessage');
        });
    });

    Route::controller(CallsAudioController::class)->group(function () {
        Route::prefix('calls/audio')->group(function () {
            Route::get('/all', 'Calls');
            Route::get('/{id}', 'Call');
            Route::post('/start_audio', 'StartCall');
            Route::post('/end_audio', 'EndCall');
            Route::delete('/{id}', 'DeleteCall');
        });
    });

    Route::controller(CallsVideoController::class)->group(function () {
        Route::prefix('calls/video')->group(function () {
            Route::get('/all', 'Calls');
            Route::get('/{id}', 'Call');
            Route::post('/start_video', 'StartCall');
            Route::post('/end_video', 'EndCall');
            Route::delete('/{id}', 'DeleteCall');
        });
    });
    // calls video routes
});
