<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ComplaintController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\FinanceController;
use App\Http\Controllers\Api\HouseController;
use App\Http\Controllers\Api\MaintenanceController;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\SearchController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/auth/otp/send', [AuthController::class, 'sendOtp']);
    Route::post('/auth/otp/verify', [AuthController::class, 'verifyOtp']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
        Route::post('/auth/fcm-token', [AuthController::class, 'updateFcmToken']);

        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::get('/dashboard/charts', [DashboardController::class, 'charts']);

        Route::apiResource('members', MemberController::class)->names([
            'index' => 'api.members.index',
            'store' => 'api.members.store',
            'show' => 'api.members.show',
            'update' => 'api.members.update',
            'destroy' => 'api.members.destroy',
        ]);
        Route::get('/houses', [HouseController::class, 'index']);
        Route::get('/houses/{house}', [HouseController::class, 'show']);
        Route::get('/houses/{house}/ledger', [HouseController::class, 'ledger']);

        Route::get('/finance/transactions', [FinanceController::class, 'index']);
        Route::post('/finance/transactions', [FinanceController::class, 'store']);
        Route::put('/finance/transactions/{transaction}', [FinanceController::class, 'update']);
        Route::delete('/finance/transactions/{transaction}', [FinanceController::class, 'destroy']);

        Route::get('/reports/balance-sheet', [FinanceController::class, 'balanceSheet']);
        Route::get('/reports/export/pdf', [FinanceController::class, 'exportPdf']);
        Route::get('/reports/export/excel', [FinanceController::class, 'exportExcel']);

        Route::apiResource('complaints', ComplaintController::class)
            ->only(['index', 'store', 'show', 'update'])
            ->names([
                'index' => 'api.complaints.index',
                'store' => 'api.complaints.store',
                'show' => 'api.complaints.show',
                'update' => 'api.complaints.update',
            ]);

        Route::get('/search', [SearchController::class, 'global']);

        Route::get('/maintenance', [MaintenanceController::class, 'index']);
        Route::get('/maintenance/cycle', [MaintenanceController::class, 'currentCycle']);
        Route::post('/maintenance/generate', [MaintenanceController::class, 'generate']);
        Route::post('/maintenance/{bill}/pay', [MaintenanceController::class, 'markPaid']);
        Route::get('/maintenance/account-report', [MaintenanceController::class, 'accountReport']);
    });
});
