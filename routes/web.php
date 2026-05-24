<?php

use App\Http\Controllers\PurchasingLite\AuthController;
use App\Http\Controllers\PurchasingLite\PurchaseRequestController;
use App\Http\Controllers\PurchasingLite\PurchaseRequestVendorController;
use App\Models\PurchaseRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/purchasing-lite');
});

Route::get('/login', function () {
    return redirect('/purchasing-lite/login');
})->name('login');

Route::prefix('purchasing-lite')->name('purchasing-lite.')->group(function () {
    Route::get('/', function () {
        if (! Auth::check()) {
            return redirect('/purchasing-lite/login');
        }

        return redirect('/purchasing-lite/dashboard');
    })->name('entry');

    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

    Route::get('/dashboard', function () {
        if (! Auth::check()) {
            return redirect('/purchasing-lite/login');
        }

        $user = Auth::user();
        $role = strtolower((string) ($user->role ?? ''));

        $baseQuery = PurchaseRequest::query()
            ->withCount('items')
            ->latest('id');

        if ($role === 'requester') {
            $baseQuery->where('requested_by', $user->id);
        } elseif ($role === 'purchasing') {
            $baseQuery->where('current_step', 'purchasing');
        } elseif ($role === 'cost_control') {
            $baseQuery->where('current_step', 'cost_control');
        } elseif ($role === 'gm') {
            $baseQuery->where('current_step', 'gm');
        } elseif ($role === 'owner') {
            $baseQuery->where('current_step', 'owner');
        } elseif ($role === 'financial_controller') {
            $baseQuery->where('current_step', 'financial_controller');
        }

        $purchaseRequests = (clone $baseQuery)
            ->limit(50)
            ->get();

        $totalPr = (clone $baseQuery)->count();

        $waitingAction = (clone $baseQuery)
            ->whereNotIn('status', [
                'completed',
                'approved',
                'rejected',
                'cancelled',
            ])
            ->count();

        return view('purchasing-lite.dashboard', [
            'user' => $user,
            'purchaseRequests' => $purchaseRequests,
            'totalPr' => $totalPr,
            'waitingAction' => $waitingAction,
        ]);
    })->name('dashboard');

    Route::get('/vendors/search', [PurchaseRequestVendorController::class, 'searchVendors'])
        ->name('vendors.search');

    Route::get('/items/search', [PurchaseRequestController::class, 'searchItems'])
        ->name('items.search');

    Route::get('/purchase-requests/create', [PurchaseRequestController::class, 'create'])
        ->name('purchase-requests.create');

    Route::post('/purchase-requests', [PurchaseRequestController::class, 'store'])
        ->name('purchase-requests.store');

    Route::post('/purchase-requests/{purchaseRequest}/submit', [PurchaseRequestController::class, 'submit'])
        ->name('purchase-requests.submit');

    Route::get('/purchase-requests/{purchaseRequest}/edit', [PurchaseRequestController::class, 'edit'])
        ->name('purchase-requests.edit');

    Route::put('/purchase-requests/{purchaseRequest}', [PurchaseRequestController::class, 'update'])
        ->name('purchase-requests.update');

    Route::get('/purchase-requests/{purchaseRequest}/vendors', [PurchaseRequestVendorController::class, 'index'])
        ->name('purchase-requests.vendors');

    Route::post('/purchase-requests/{purchaseRequest}/vendors', [PurchaseRequestVendorController::class, 'store'])
        ->name('purchase-requests.vendors.store');

    Route::post('/purchase-requests/{purchaseRequest}/vendors/send-to-cost-control', [PurchaseRequestVendorController::class, 'sendToCostControl'])
        ->name('purchase-requests.vendors.send-to-cost-control');

    Route::post('/purchase-requests/{purchaseRequest}/vendors/send-back-to-requester', [PurchaseRequestVendorController::class, 'sendBackToRequester'])
        ->name('purchase-requests.vendors.send-back-to-requester');

    Route::post('/purchase-requests/{purchaseRequest}/vendors/reject-to-requester', [PurchaseRequestVendorController::class, 'rejectToRequester'])
        ->name('purchase-requests.vendors.reject-to-requester');

    Route::get('/purchase-requests/{purchaseRequest}', [PurchaseRequestController::class, 'show'])
        ->name('purchase-requests.show');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
