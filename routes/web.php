<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SurgeryRequestController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CalendarController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Inertia\Inertia;

Route::redirect('/', '/login');

Route::get('/dashboard', function (Request $request) {
    if ($request->user()->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    }
    if ($request->user()->hasRole('medico')) {
        return redirect()->route('medico.dashboard');
    }
    if ($request->user()->hasRole('enfermeiro')) {
        return redirect()->route('enfermeiro.dashboard');
    }

    abort(403);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth','verified','role:admin'])
    ->get('/admin/dashboard',[DashboardController::class,'admin'])
    ->name('admin.dashboard');

Route::middleware(['auth','verified','role:admin'])->group(function () {
    Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users.index');
    Route::get('/admin/users/create', [UserController::class, 'create'])->name('admin.users.create');
    Route::post('/admin/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::get('/admin/users/{user}', [UserController::class, 'show'])->name('admin.users.show');
    Route::get('/admin/users/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/admin/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/admin/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
});

Route::middleware(['auth','verified','role:medico'])
    ->get('/medico/dashboard',[DashboardController::class,'medico'])
    ->name('medico.dashboard');

Route::middleware(['auth','verified','role:enfermeiro'])
    ->get('/enfermeiro/dashboard',[DashboardController::class,'enfermeiro'])
    ->name('enfermeiro.dashboard');

Route::middleware(['auth','role:medico|enfermeiro|admin'])->group(function () {
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar');
    Route::post('/calendar', [CalendarController::class, 'store'])->name('day-reservations.store');
    Route::post('/calendar/{dayReservation}/confirm', [CalendarController::class, 'confirm'])->name('day-reservations.confirm');
    Route::delete('/calendar/{dayReservation}', [CalendarController::class, 'destroy'])->name('day-reservations.destroy');
});

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
        Route::delete('/surgery-requests/{surgeryRequest}', [SurgeryRequestController::class, 'destroy'])
            ->name('surgery-requests.destroy');
    });

    // edição/atualização do pedido
    Route::middleware(['role:medico|enfermeiro|admin'])->group(function () {
        Route::get('/surgery-requests/{surgeryRequest}/edit', [SurgeryRequestController::class, 'edit'])
            ->name('surgery-requests.edit');
        Route::match(['put', 'patch'], '/surgery-requests/{surgeryRequest}', [SurgeryRequestController::class, 'update'])
            ->name('surgery-requests.update');

        Route::post('/surgery-requests/{surgeryRequest}/documents', [DocumentController::class, 'store'])
            ->name('surgery-requests.documents.store');
        Route::delete('/surgery-requests/{surgeryRequest}/documents/{document}', [DocumentController::class, 'destroy'])
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
        Route::put('/surgery-requests/{surgeryRequest}/checklist-items/{item}', [SurgeryRequestController::class, 'updateChecklistItem'])
            ->name('surgery-requests.checklist-items.update');

        // aprovar / reprovar
        Route::post('/surgery-requests/{surgeryRequest}/approve', [SurgeryRequestController::class, 'approve'])
            ->name('surgery-requests.approve');
        Route::post('/surgery-requests/{surgeryRequest}/reject', [SurgeryRequestController::class, 'reject'])
            ->name('surgery-requests.reject');

        // CRUD de checklists (modelos)
        Route::resource('checklists', ChecklistController::class)->except(['show']);
    });
});

require __DIR__.'/auth.php';
