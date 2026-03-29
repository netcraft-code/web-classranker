<?php

use CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\Core\CmsController;
use CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\Customer\AuthController;
use CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\StudyMaterial\BoardController;
use CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\StudyMaterial\BookController;
use CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\StudyMaterial\BookmarkController;
use CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\StudyMaterial\ChapterController;
use CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\StudyMaterial\GradeController;
use CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\StudyMaterial\NoteController;
use CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\StudyMaterial\PdfController;
use CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\StudyMaterial\QuestionController;
use CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\StudyMaterial\QuizController;
use CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\StudyMaterial\SubjectController;
use CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\StudyMaterial\VideoController;
use Illuminate\Support\Facades\Route;

/**
 * Customer unauthorized routes.
 */
Route::controller(AuthController::class)->prefix('customer')->group(function () {
    Route::post('login', 'login');

    Route::post('send-otp', 'sendOtp');

    Route::post('verify-otp', 'verifyOtp');

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
});
