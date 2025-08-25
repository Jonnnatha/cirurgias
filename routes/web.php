<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SurgeryRequestController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\DocumentController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::redirect('/', '/login');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Perfil do usuário (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /**
     * ===========================================
     *  ROTAS DO MÉDICO (e Admin)
     *  - Criar pedido de cirurgia
     *  - Listar pedidos do próprio médico
     * ===========================================
     */
    Route::middleware(['role:medico|admin'])->group(function () {
        // formulário de criação
        Route::get('/surgery-requests/create', [SurgeryRequestController::class, 'create'])
            ->name('surgery-requests.create');

        // cria pedido (status: requested/laranja)
        Route::post('/surgery-requests', [SurgeryRequestController::class, 'store'])
            ->name('surgery-requests.store');

        // lista somente do médico logado
        Route::get('/my/surgery-requests', [SurgeryRequestController::class, 'indexMy'])
            ->name('surgery-requests.indexMy');

        // cancelar/remover pedido
        Route::delete('/surgery-requests/{request}', [SurgeryRequestController::class, 'destroy'])
            ->name('surgery-requests.destroy');
    });

    // edição/atualização do pedido
    Route::middleware(['role:medico|enfermeiro|admin'])->group(function () {
        Route::get('/surgery-requests/{request}/edit', [SurgeryRequestController::class, 'edit'])
            ->name('surgery-requests.edit');
        Route::match(['put', 'patch'], '/surgery-requests/{request}', [SurgeryRequestController::class, 'update'])
            ->name('surgery-requests.update');

        Route::post('/surgery-requests/{request}/documents', [DocumentController::class, 'store'])
            ->name('surgery-requests.documents.store');
        Route::delete('/surgery-requests/{request}/documents/{document}', [DocumentController::class, 'destroy'])
            ->name('surgery-requests.documents.destroy');
    });

    /**
     * ===========================================
     *  ROTAS DO ENFERMEIRO (e Admin)
     *  - Ver pendentes/agenda
     *  - Marcar itens de checklist
     *  - Aprovar/Reprovar pedido
     *  - Gerenciar modelos de checklist
     * ===========================================
     */
    Route::middleware(['role:enfermeiro|admin'])->group(function () {
        // listar/filtros para a enfermagem
        Route::get('/surgery-requests', [SurgeryRequestController::class, 'index'])
            ->name('surgery-requests.index');

        // marcar item do checklist (autorização via Policy do pedido pai)
        Route::put('/surgery-requests/{request}/checklist-items/{item}', [SurgeryRequestController::class, 'updateChecklistItem'])
            ->name('surgery-requests.checklist-items.update');

        // aprovar / reprovar
        Route::post('/surgery-requests/{request}/approve', [SurgeryRequestController::class, 'approve'])
            ->name('surgery-requests.approve');
        Route::post('/surgery-requests/{request}/reject', [SurgeryRequestController::class, 'reject'])
            ->name('surgery-requests.reject');

        // CRUD de checklists (modelos)
        Route::resource('checklists', ChecklistController::class)->except(['show']);
    });
});

require __DIR__.'/auth.php';
