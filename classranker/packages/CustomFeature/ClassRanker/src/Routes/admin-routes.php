<?php

use CustomFeature\Board\Http\Controllers\BoardController;
use CustomFeature\Book\Http\Controllers\BookController;
use CustomFeature\Chapter\Http\Controllers\ChapterController;
use CustomFeature\ClassRanker\Http\Controllers\Dashboard\DashboardController;
use CustomFeature\ClassRanker\Http\Controllers\TempUploadController;
use CustomFeature\Grade\Http\Controllers\GradeController;
use CustomFeature\Note\Http\Controllers\NoteController;
use CustomFeature\Pdf\Http\Controllers\PdfController;
use CustomFeature\Question\Http\Controllers\QuestionController;
use CustomFeature\Quiz\Http\Controllers\QuizController;
use CustomFeature\Subject\Http\Controllers\SubjectController;
use CustomFeature\Video\Http\Controllers\VideoController;
use Illuminate\Support\Facades\Route;

/**
 * Catalog routes.
 */
Route::prefix('study-material')->group(function () {
    Route::prefix('subjects')->group(function () {
        /**
         * Boards routes.
         */
        Route::controller(BoardController::class)->prefix('boards')->group(function () {
            Route::get('', 'index')->name('admin.study_materials.subjects.boards.index');

            Route::post('', 'store')->name('admin.study_materials.subjects.boards.store');

            Route::get('{id}', 'edit')->name('admin.study_materials.subjects.boards.edit');

            Route::put('{id}', 'update')->name('admin.study_materials.subjects.boards.update');

            Route::delete('{id}', 'delete')->name('admin.study_materials.subjects.boards.delete');
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
    Route::prefix('questions')->group(function () {
        Route::post('temp-upload', [TempUploadController::class, 'store'])
            ->name('admin.study_materials.temp_upload.store');

        Route::controller(QuestionController::class)->prefix('questions')->group(function () {
            Route::get('', 'index')->name('admin.study_materials.questions.index');
            
            Route::post('create', 'store')->name('admin.study_materials.questions.store');

            Route::get('edit/{id}', 'edit')->name('admin.study_materials.questions.edit');

            Route::put('edit/{id}', 'update')->name('admin.study_materials.questions.update');

            Route::delete('edit/{id}', 'delete')->name('admin.study_materials.questions.delete');

            Route::post('{id}/assignments/add', 'addAssignment')->name('admin.study_materials.questions.assignments.add');

            Route::delete('{id}/assignments/{assignmentId}', 'removeAssignment')->name('admin.study_materials.questions.assignments.remove');

            Route::post('{id}/question-items/add', 'addQuestionItem')->name('admin.study_materials.questions.items.add');

            Route::put('{id}/question-items/{itemId}', 'updateQuestionItem')->name('admin.study_materials.questions.items.update');

            Route::delete('{id}/question-items/{itemId}', 'removeQuestionItem')->name('admin.study_materials.questions.items.remove');

            Route::post('{id}/faqs/add', 'addFaq')->name('admin.study_materials.questions.faqs.add');

            Route::put('{id}/faqs/{faqId}', 'updateFaq')->name('admin.study_materials.questions.faqs.update');
            
            Route::delete('{id}/faqs/{faqId}', 'removeFaq')->name('admin.study_materials.questions.faqs.remove');
        });

        Route::controller(VideoController::class)->prefix('videos')->group(function () {
            Route::get('', 'index')->name('admin.study_materials.videos.index');
            
            Route::post('create', 'store')->name('admin.study_materials.videos.store');

            Route::get('edit/{id}', 'edit')->name('admin.study_materials.videos.edit');

            Route::put('edit/{id}', 'update')->name('admin.study_materials.videos.update');

            Route::delete('edit/{id}', 'delete')->name('admin.study_materials.videos.delete');

            Route::post('{id}/assignments/add', 'addAssignment')->name('admin.study_materials.videos.assignments.add');
            
            Route::delete('{id}/assignments/{assignmentId}', 'removeAssignment')->name('admin.study_materials.videos.assignments.remove');

            Route::post('{id}/video-items/add', 'addVideoItem')->name('admin.study_materials.videos.items.add');
            
            Route::post('{id}/video-items/{itemId}', 'updateVideoItem')->name('admin.study_materials.videos.items.update');
            
            Route::delete('{id}/video-items/{itemId}', 'removeVideoItem')->name('admin.study_materials.videos.items.remove');
        });

        /**
         * Pdfs routes
         */
        Route::controller(PdfController::class)->prefix('pdfs')->group(function () {
            Route::get('', 'index')->name('admin.study_materials.pdfs.index');
            
            Route::post('create', 'store')->name('admin.study_materials.pdfs.store');

            Route::get('edit/{id}', 'edit')->name('admin.study_materials.pdfs.edit');

            Route::put('edit/{id}', 'update')->name('admin.study_materials.pdfs.update');

            Route::delete('edit/{id}', 'delete')->name('admin.study_materials.pdfs.delete');

            Route::post('{id}/assignments/add', 'addAssignment')->name('admin.study_materials.pdfs.assignments.add');
            
            Route::delete('{id}/assignments/{assignmentId}', 'removeAssignment')->name('admin.study_materials.pdfs.assignments.remove');

            Route::post('{id}/pdf-items/add', 'addPdfItem')->name('admin.study_materials.pdfs.items.add');
            
            Route::post('{id}/pdf-items/{itemId}', 'updatePdfItem')->name('admin.study_materials.pdfs.items.update');
            
            Route::delete('{id}/pdf-items/{itemId}', 'removePdfItem')->name('admin.study_materials.pdfs.items.remove');
        });
        
        /**
         * Quizzes routes
         */
        Route::controller(QuizController::class)->prefix('quizzes')->group(function () {
            Route::get('', 'index')->name('admin.study_materials.quizzes.index');
            
            Route::post('create', 'store')->name('admin.study_materials.quizzes.store');

            Route::get('edit/{id}', 'edit')->name('admin.study_materials.quizzes.edit');

            Route::put('edit/{id}', 'update')->name('admin.study_materials.quizzes.update');

            Route::delete('edit/{id}', 'delete')->name('admin.study_materials.quizzes.delete');
            
            // AJAX routes
            Route::post('{id}/chapters/add', 'addChapter')->name('admin.study_materials.quizzes.chapters.add');
            
            Route::delete('{id}/chapters/{chapterId}', 'removeChapter')->name('admin.study_materials.quizzes.chapters.remove');

            Route::post('{id}/questions/add', 'addQuestion')->name('admin.study_materials.quizzes.questions.add');
            
            Route::put('{id}/questions/{questionId}', 'updateQuestion')->name('admin.study_materials.quizzes.questions.update');
            
            Route::delete('{id}/questions/{questionId}', 'removeQuestion')->name('admin.study_materials.quizzes.questions.remove');
        });

        /**
         * Notes routes
         */
        Route::controller(NoteController::class)->prefix('notes')->group(function () {
            Route::get('', 'index')->name('admin.study_materials.notes.index');

            Route::get('create', 'create')->name('admin.study_materials.notes.create');
            
            Route::post('create', 'store')->name('admin.study_materials.notes.store');

            Route::get('edit/{id}', 'edit')->name('admin.study_materials.notes.edit');

            Route::put('edit/{id}', 'update')->name('admin.study_materials.notes.update');

            Route::delete('edit/{id}', 'delete')->name('admin.study_materials.notes.delete');
        });
    });
});

/**
 * Dashboard routes.
 */
Route::controller(DashboardController::class)->prefix('dashboards')->group(function () {
    Route::get('', 'index')->name('admin.class_ranker.dashboard.index');
});
