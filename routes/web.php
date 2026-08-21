<?php

use App\Livewire\Announcements\Index as AnnouncementsIndex;
use App\Livewire\Auth\Login;
use App\Livewire\Complaints\Index as ComplaintsIndex;
use App\Livewire\Dashboard\Index as DashboardIndex;
use App\Livewire\Documents\Index as DocumentsIndex;
use App\Livewire\Finance\Index as FinanceIndex;
use App\Livewire\Houses\Index as HousesIndex;
use App\Livewire\Maintenance\Index as MaintenanceIndex;
use App\Livewire\Members\Index as MembersIndex;
use App\Livewire\Parking\Index as ParkingIndex;
use App\Livewire\Polls\Index as PollsIndex;
use App\Livewire\Reports\AccountReport;
use App\Livewire\Visitors\Index as VisitorsIndex;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/locale/{locale}', function (string $locale) {
    if (in_array($locale, ['en', 'gu'])) {
        session(['locale' => $locale]);
        if (auth()->check()) {
            auth()->user()->update(['locale' => $locale]);
        }
    }

    return back();
})->name('locale.switch');

Route::post('/dark-toggle', function () {
    session(['dark_mode' => ! session('dark_mode', false)]);

    return response()->noContent();
})->name('dark.toggle');

Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::redirect('/', '/login');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardIndex::class)->name('dashboard');
    Route::get('/members', MembersIndex::class)->name('members.index');
    Route::get('/finance', FinanceIndex::class)->name('finance.index');
    Route::get('/complaints', ComplaintsIndex::class)->name('complaints.index');
    Route::get('/houses', HousesIndex::class)->name('houses.index');
    Route::get('/reports', AccountReport::class)->name('reports.index');
    Route::get('/announcements', AnnouncementsIndex::class)->name('announcements.index');
    Route::get('/polls', PollsIndex::class)->name('polls.index');
    Route::get('/visitors', VisitorsIndex::class)->name('visitors.index');
    Route::get('/documents', DocumentsIndex::class)->name('documents.index');
    Route::get('/maintenance', MaintenanceIndex::class)->name('maintenance.index');
    Route::get('/parking', ParkingIndex::class)->name('parking.index');
    Route::get('/more', fn () => view('livewire.pages.more', ['title' => __('app.settings')]))->name('more');

    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect('/login');
    })->name('logout');
});
