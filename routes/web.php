<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\RoadmapController;
use App\Http\Controllers\RoadmapStepController;
use App\Http\Controllers\MemoController;
use App\Http\Controllers\MemoFolderController;
use App\Http\Controllers\MemoAttachmentController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DevLabController;
use App\Http\Controllers\DevLabFileController;
use App\Http\Controllers\DevLabProjectController;
use App\Http\Controllers\SandboxController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('home');

Route::get('/sitemap.xml', function () {
    $urls = [route('home')];
    $xml = '<?xml version="1.0" encoding="UTF-8"?>';
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    foreach ($urls as $url) {
        $xml .= '<url><loc>' . e($url) . '</loc></url>';
    }
    $xml .= '</urlset>';

    return response($xml, 200, [
        'Content-Type' => 'application/xml; charset=UTF-8',
        'Cache-Control' => 'public, max-age=3600',
    ]);
})->name('sitemap');

Route::get('/robots.txt', function () {
    $sitemap = route('sitemap');

    return response(
        "# DevRoad\nUser-agent: *\nAllow: /\nDisallow: /dashboard\nDisallow: /profile\nDisallow: /devlab\nDisallow: /sandbox\nDisallow: /memos\nDisallow: /roadmaps\nDisallow: /steps\nSitemap: {$sitemap}\n",
        200,
        ['Content-Type' => 'text/plain; charset=UTF-8']
    );
})->name('robots');

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/preferences', [ProfileController::class, 'updatePreferences'])->name('profile.preferences');
    Route::patch('/profile/theme', [ProfileController::class, 'updateTheme'])->name('profile.theme');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/devlab', DevLabController::class)->name('devlab');
    Route::get('/sandbox', [SandboxController::class, 'index'])->name('sandbox');

    Route::prefix('sandbox/projects')->name('sandbox.projects.')->group(function () {
        Route::post('/', [SandboxController::class, 'store'])->name('store');
        Route::get('/{project}', [SandboxController::class, 'show'])->name('show');
        Route::patch('/{project}', [SandboxController::class, 'update'])->name('update');
        Route::delete('/{project}', [SandboxController::class, 'destroy'])->name('destroy');
        Route::post('/{project}/start', [SandboxController::class, 'start'])->name('start');
        Route::post('/{project}/stop', [SandboxController::class, 'stop'])->name('stop');
        Route::post('/{project}/restart', [SandboxController::class, 'restart'])->name('restart');
        Route::post('/{project}/command', [SandboxController::class, 'command'])->middleware('throttle:30,1')->name('command');
        Route::post('/{project}/terminal', [SandboxController::class, 'terminal'])->middleware('throttle:20,1')->name('terminal');
        Route::get('/{project}/status', [SandboxController::class, 'status'])->name('status');
    });

    Route::prefix('devlab/projects')->name('devlab.projects.')->group(function () {
        Route::get('/', [DevLabProjectController::class, 'index'])->name('index');
        Route::get('/csrf-token', function (\Illuminate\Http\Request $request) {
            return response()->json(
                ['token' => $request->session()->token()],
                200,
                ['Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0']
            );
        })->name('csrf-token');
        Route::post('/', [DevLabProjectController::class, 'store'])->name('store');
        Route::post('/import-legacy', [DevLabProjectController::class, 'importLegacy'])->name('import-legacy');
        Route::post('/for-step/{step}', [DevLabProjectController::class, 'openForStep'])->name('for-step');
        Route::get('/{project}', [DevLabProjectController::class, 'show'])->name('show');
        Route::patch('/{project}', [DevLabProjectController::class, 'update'])->name('update');
        Route::delete('/{project}', [DevLabProjectController::class, 'destroy'])->name('destroy');
        Route::post('/{project}/duplicate', [DevLabProjectController::class, 'duplicate'])->name('duplicate');
        Route::post('/{project}/files', [DevLabFileController::class, 'store'])->name('files.store');
        Route::patch('/{project}/files/{file}', [DevLabFileController::class, 'update'])->name('files.update');
        Route::delete('/{project}/files/{file}', [DevLabFileController::class, 'destroy'])->name('files.destroy');
    });

    Route::resource('roadmaps', RoadmapController::class);
    Route::resource('roadmaps.steps', RoadmapStepController::class)->only(['store', 'update', 'destroy'])->shallow();
    Route::get('steps/{step}', [RoadmapStepController::class, 'show'])->name('steps.show');
    Route::patch('steps/{step}/status', [RoadmapStepController::class, 'updateStatus'])->name('steps.status');
    Route::patch('steps/{step}/exercise', [RoadmapStepController::class, 'updateExercise'])->name('steps.exercise');
    Route::post('steps/{step}/run', [RoadmapStepController::class, 'runCode'])->name('steps.run');
    Route::post('steps/{step}/memo', [MemoController::class, 'storeFromStep'])->name('steps.memo');
    Route::post('memo-folders', [MemoFolderController::class, 'store'])->name('memo-folders.store');
    Route::patch('memo-folders/{folder}', [MemoFolderController::class, 'update'])->name('memo-folders.update');
    Route::delete('memo-folders/{folder}', [MemoFolderController::class, 'destroy'])->name('memo-folders.destroy');
    Route::get('memos/attachments/{attachment}', [MemoAttachmentController::class, 'show'])->name('memos.attachments.show');
    Route::get('memos/attachments/{attachment}/download', [MemoAttachmentController::class, 'download'])->name('memos.attachments.download');
    Route::delete('memos/attachments/{attachment}', [MemoAttachmentController::class, 'destroy'])->name('memos.attachments.destroy');
    Route::post('memos/{memo}/attachments', [MemoAttachmentController::class, 'store'])->name('memos.attachments.store');
    Route::resource('memos', MemoController::class);
    Route::patch('memos/{memo}/favorite', [MemoController::class, 'toggleFavorite'])->name('memos.favorite');
    Route::get('search', SearchController::class)->name('search');
});

require __DIR__.'/auth.php';
