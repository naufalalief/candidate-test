<?php

use App\Http\Controllers\ImportExportController;
use App\Http\Controllers\LayerController;
use App\Http\Controllers\LayupController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SupplierController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function (Request $request) {
    $supplierCount = \App\Models\Supplier::count();
    $layupCount = \App\Models\Layup::count();
    $layerCount = \App\Models\Layer::count();

    $recentSuppliers = \App\Models\Supplier::withCount('layups')
        ->latest()
        ->take(5)
        ->get();

    $recentLayups = \App\Models\Layup::with('supplier')
        ->withCount('layers')
        ->latest()
        ->take(5)
        ->get();

    $notifications = $request->user()->notifications()->take(5)->get();

    return view('dashboard', compact(
        'supplierCount',
        'layupCount',
        'layerCount',
        'recentSuppliers',
        'recentLayups',
        'notifications',
    ));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('suppliers', SupplierController::class);
    Route::get('suppliers-export', [ImportExportController::class, 'exportAll'])->name('suppliers.export.all');
    Route::get('suppliers-import', [ImportExportController::class, 'importAllForm'])->name('suppliers.import.all');
    Route::post('suppliers-import/preview', [ImportExportController::class, 'importAllPreview'])->name('suppliers.import.all.preview');
    Route::post('suppliers-import/resolve', [ImportExportController::class, 'resolveAllConflicts'])->name('suppliers.import.all.resolve');
    Route::get('suppliers/{supplier}/export', [ImportExportController::class, 'export'])->name('suppliers.export');
    Route::get('suppliers/{supplier}/import', [ImportExportController::class, 'importForm'])->name('suppliers.import');
    Route::post('suppliers/{supplier}/import/preview', [ImportExportController::class, 'importPreview'])->name('suppliers.import.preview');
    Route::post('suppliers/{supplier}/import/resolve', [ImportExportController::class, 'resolveConflicts'])->name('suppliers.import.resolve');
    Route::resource('suppliers.layups', LayupController::class);
    Route::resource('suppliers.layups.layers', LayerController::class);

    Route::post('/notifications/mark-read', [NotificationController::class, 'markAllRead'])->name('notifications.markRead');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
});

require __DIR__.'/auth.php';
