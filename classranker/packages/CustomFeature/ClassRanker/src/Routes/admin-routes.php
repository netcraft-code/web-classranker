<?php

use Illuminate\Support\Facades\Route;
use CustomFeature\ClassRanker\Http\Controllers\StudyMaterial\SubjectController;
use CustomFeature\ClassRanker\Http\Controllers\StudyMaterial\GradeController;
use CustomFeature\ClassRanker\Http\Controllers\StudyMaterial\BoardController;
use CustomFeature\ClassRanker\Http\Controllers\StudyMaterial\BookController;
use CustomFeature\ClassRanker\Http\Controllers\StudyMaterial\ChapterController;
use CustomFeature\ClassRanker\Http\Controllers\StudyMaterial\QuestionController;
use CustomFeature\ClassRanker\Http\Controllers\StudyMaterial\QuizController;
use CustomFeature\ClassRanker\Http\Controllers\DashboardController;

/**
 * Catalog routes.
 */
Route::prefix('study-material')->group(function () {
    Route::prefix('subjects')->group(function () {
        /**
         * Subjects routes.
         */
        Route::controller(SubjectController::class)->prefix('subjects')->group(function () {
            Route::get('', 'index')->name('admin.study_materials.subjects.subjects.index');

            Route::get('create', 'create')->name('admin.study_materials.subjects.subjects.create');

            Route::post('create', 'store')->name('admin.study_materials.subjects.subjects.store');

            Route::get('edit/{id}', 'edit')->name('admin.study_materials.subjects.subjects.edit');

            Route::put('edit/{id}', 'update')->name('admin.study_materials.subjects.subjects.update');

            Route::delete('edit/{id}', 'delete')->name('admin.study_materials.subjects.subjects.delete');

            Route::post('mass-delete', 'massdelete')->name('admin.study_materials.subjects.subjects.mass_delete');
        });

        /**
         * Grades routes.
         */
        Route::controller(GradeController::class)->prefix('grades')->group(function () {
            Route::get('', 'index')->name('admin.study_materials.subjects.grades.index');

            Route::get('create', 'create')->name('admin.study_materials.subjects.grades.create');

            Route::post('create', 'store')->name('admin.study_materials.subjects.grades.store');

            Route::get('edit/{id}', 'edit')->name('admin.study_materials.subjects.grades.edit');

            Route::put('edit/{id}', 'update')->name('admin.study_materials.subjects.grades.update');

            Route::delete('edit/{id}', 'delete')->name('admin.study_materials.subjects.grades.delete');
        });

        /**
         * Boards routes.
         */
        Route::controller(BoardController::class)->prefix('boards')->group(function () {
            Route::get('', 'index')->name('admin.study_materials.subjects.boards.index');

            Route::get('create', 'create')->name('admin.study_materials.subjects.boards.create');

            Route::post('create', 'store')->name('admin.study_materials.subjects.boards.store');

            Route::get('edit/{id}', 'edit')->name('admin.study_materials.subjects.boards.edit');

            Route::put('edit/{id}', 'update')->name('admin.study_materials.subjects.boards.update');

            Route::delete('edit/{id}', 'delete')->name('admin.study_materials.subjects.boards.delete');
        });
    });

    /**
     * Books routes.
     */
    Route::controller(BookController::class)->prefix('books')->group(function () {
        Route::get('', 'index')->name('admin.study_materials.books.index');

        Route::get('create', 'create')->name('admin.study_materials.books.create');

        Route::post('create', 'store')->name('admin.study_materials.books.store');

        Route::get('edit/{id}', 'edit')->name('admin.study_materials.books.edit');

        Route::put('edit/{id}', 'update')->name('admin.study_materials.books.update');

        Route::delete('edit/{id}', 'delete')->name('admin.study_materials.books.delete');
    });

    /**
     * Chapters routes.
     */
    Route::controller(ChapterController::class)->prefix('chapters')->group(function () {
        Route::get('', 'index')->name('admin.study_materials.chapters.index');

        Route::get('create', 'create')->name('admin.study_materials.chapters.create');
        
        Route::post('create', 'store')->name('admin.study_materials.chapters.store');

        Route::get('edit/{id}', 'edit')->name('admin.study_materials.chapters.edit');

        Route::put('edit/{id}', 'update')->name('admin.study_materials.chapters.update');

        Route::delete('edit/{id}', 'delete')->name('admin.study_materials.chapters.delete');
    });

    /**
     * Questions routes.
     */
    Route::controller(QuestionController::class)->prefix('questions')->group(function () {
        Route::get('', 'index')->name('admin.study_materials.questions.index');

        Route::get('create', 'create')->name('admin.study_materials.questions.create');
        
        Route::post('create', 'store')->name('admin.study_materials.questions.store');

        Route::get('edit/{id}', 'edit')->name('admin.study_materials.questions.edit');

        Route::put('edit/{id}', 'update')->name('admin.study_materials.questions.update');

        Route::delete('edit/{id}', 'delete')->name('admin.study_materials.questions.delete');
    });

    /**
     * Quizzes routes.
     */
    Route::controller(QuizController::class)->prefix('quizzes')->group(function () {
        Route::get('', 'index')->name('admin.study_materials.quizzes.index');

        Route::get('create', 'create')->name('admin.study_materials.quizzes.create');
        
        Route::post('create', 'store')->name('admin.study_materials.quizzes.store');

        Route::get('edit/{id}', 'edit')->name('admin.study_materials.quizzes.edit');

        Route::put('edit/{id}', 'update')->name('admin.study_materials.quizzes.update');

        Route::delete('edit/{id}', 'delete')->name('admin.study_materials.quizzes.delete');

        Route::get('bulk-upload', 'bulkUpload')->name('admin.study_materials.quizzes.bulk-upload');
        
        Route::post('parse-file', 'parseFile')->name('admin.study_materials.quizzes.parse-file');
        
        Route::post('bulk-store', 'bulkStore')->name('admin.study_materials.quizzes.bulk-store');
    });
});

/**
 * Dashboard routes.
 */
Route::controller(DashboardController::class)->prefix('dashboards')->group(function () {
    Route::get('', 'index')->name('admin.class_ranker.dashboard.index');
});
