<?php

use CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\Ads\AdController;
use CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\Core\CmsController;
use CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\Customer\AuthController;
use CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\Discussion\DiscussionCommentController;
use CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\Discussion\DiscussionController;
use CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\Plan\PlanController;
use CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\StudyMaterial\BoardController;
use CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\StudyMaterial\BookController;
use CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\StudyMaterial\BookmarkController;
use CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\StudyMaterial\ChapterController;
use CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\StudyMaterial\GradeController;
use CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\StudyMaterial\NoteController;
use CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\StudyMaterial\PdfController;
use CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\StudyMaterial\QuestionController;
use CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\StudyMaterial\QuizController;
use CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\StudyMaterial\RecentlyViewedController;
use CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\StudyMaterial\SubjectController;
use CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\StudyMaterial\VideoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * Customer unauthorized routes.
 */
Route::controller(AuthController::class)->prefix('customer')->group(function () {
    Route::post('login', 'login');

    Route::post('send-otp', 'sendOtp');

    Route::get('resend-otp', 'resendOtp');

    Route::post('verify-otp', 'verifyOtp');

    Route::post('check-user', 'checkUser');

    Route::post('verify-access-token', 'verifyAccessToken');

    Route::post('register', 'register');
});

/**
 * Customer authorized routes.
 */
Route::group(['middleware' => ['auth:sanctum', 'sanctum.customer']], function () {
    /**
     * Customer auth routes.
     */
    Route::controller(AuthController::class)->prefix('customer')->group(function () {
        Route::get('me', 'me');

        Route::get('get', 'get');

        Route::put('profile-update', 'updateProfile');

        Route::put('profile-address', 'updateAddress');

        Route::put('profile-password', 'updatePassword');

        Route::post('logout', 'logout');

        Route::post('board-class', 'updateBoardClass');
    });

    Route::controller(PlanController::class)->prefix('plans')->group(function () {
        Route::get('', 'allResources');

        Route::post('{id}/create-order', 'createOrder');

        Route::post('verify-payment', 'verifyPayment');

        Route::get('my-plans', 'myPlans');
    });
    
    Route::controller(BoardController::class)->prefix('boards')->group(function () {
        Route::get('', 'allResources');
    });

    Route::controller(GradeController::class)->prefix('grades')->group(function () {
        Route::get('', 'allResources');
    });

    Route::controller(SubjectController::class)->prefix('subjects')->group(function () {
        Route::get('', 'allResources');
    });

    Route::controller(BookController::class)->prefix('books')->group(function () {
        Route::get('', 'allResources');

        Route::get('quiz-video-list', 'quizVideoList');
    });

    Route::controller(ChapterController::class)->prefix('chapters')->group(function () {
        Route::get('', 'allResources');

        Route::get('{id}', 'getResource');
    });

    Route::controller(QuestionController::class)->prefix('questions')->group(function () {
        Route::get('', 'allResources');

        Route::get('items', 'getResourceItems');

        Route::get('{id}', 'getResource');
    });

    Route::controller(NoteController::class)->prefix('notes')->group(function () {
        Route::get('', 'allResources');

        Route::get('{id}', 'getResource');
    });

    Route::controller(VideoController::class)->prefix('videos')->group(function () {
        Route::get('', 'allResources');

        Route::get('{id}', 'getResource');
    });

    Route::controller(PdfController::class)->prefix('pdfs')->group(function () {
        Route::get('', 'allResources');

        Route::get('{id}', 'getResource');

        Route::get('file/{itemid}', 'streamFile');
    });

    Route::controller(QuizController::class)->prefix('quizzes')->group(function () {
        Route::get('', 'allResources');

        Route::get('{id}', 'getResource');

        Route::post('{id}', 'submitQuiz');
    });

    Route::controller(CmsController::class)->prefix('cms')->group(function () {
        Route::get('', 'allResources');

        Route::get('{id}', 'getResource');
    });

    Route::controller(VideoController::class)->prefix('short-videos')->group(function () {
        Route::get('', 'allResources');

        Route::get('{id}', 'getResource');

        Route::get('subjects', 'subjectChapterList');
    });

    Route::controller(BookmarkController::class)->prefix('bookmarks')->group(function () {
        Route::get('', 'allResources');
        
        Route::post('toggle', 'toggle');
    });

    Route::controller(RecentlyViewedController::class)->prefix('recently-viewed')->group(function () {
        Route::get('', 'allResources');

        Route::post('', 'record');
    });

    Route::controller(AdController::class)->prefix('ads')->group(function () {
        Route::post('watch', 'watchAd');

        Route::get('premium-status', 'premiumStatus');
    });

    Route::controller(DiscussionController::class)->prefix('discussions')->group(function () {
        Route::get('', 'allResources');

        Route::get('search', 'searchTitles');

        Route::get('hashtags', 'hashtags');

        Route::get('{id}', 'getResource');

        Route::post('', 'store');

        Route::post('{id}/like', 'toggleLike');

        Route::get('/{id}/likes', 'likes');
    });

    Route::controller(DiscussionCommentController::class)->prefix('discussions')->group(function () {
        Route::post('{discussionId}/comments', 'store');

        Route::delete('{discussionId}/comments/{commentId}', 'destroy');

        Route::post('{discussionId}/comments/{commentId}/like', 'toggleLike');

        Route::patch('{discussionId}/comments/{commentId}', 'update');
        
        Route::post('{id}/ai-comment', 'aiGenerate');
        
        Route::get('{id}/comments', 'getComments');
        
        Route::post('comments/upload-image', 'uploadTempImage');

        Route::post('{id}/mark-correct', 'markCorrectAnswers');
    });
});

Route::post('/payu/store-data', function (Request $request) {
    $token = Str::random(40);
    
    Cache::put('payu_'.$token, $request->all(), now()->addMinutes(10));

    return response()->json([
        'success' => true,
        'token' => $token
    ]);
});

Route::get('/payu/redirect-form', function (Request $request) {
    $token = $request->query('token');
    $data  = Cache::get('payu_'.$token);

    if (!$data) {
        return "Payment session expired";
    }

    return view('class_ranker_api::payu-form', compact('data'));
});

Route::post('payu/success', function (Request $request) {
    $params = http_build_query($request->all());

    return redirect("classranker://payment-success?{$params}");
});

Route::post('payu/failure', function (Request $request) {
    $params = http_build_query($request->all());

    return redirect("classranker://payment-failure?{$params}");
});
