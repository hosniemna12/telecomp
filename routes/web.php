<?php


use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Auth\Login;
use App\Livewire\Fichiers\Upload;
use App\Livewire\Fichiers\Index;
use App\Livewire\Fichiers\Show;
use App\Livewire\Fichiers\XmlViewer;
use App\Livewire\Rejets\Index as RejetsIndex;
use App\Livewire\Users\Index as UsersIndex;
use App\Livewire\Stats\Index as StatsIndex;
use App\Livewire\Profile\Index as ProfileIndex;
use App\Livewire\Outils\VerificateurRib;
use App\Livewire\Profile\Show as ProfileShow;
use App\Livewire\Audit\Index as AuditIndex;



Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', Login::class)->name('login');

Route::post('/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect()->route('login');
})->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', App\Livewire\Dashboard::class)
        ->name('dashboard');

    Route::get('/fichiers/upload', Upload::class)
        ->middleware('role:admin,operateur')
        ->name('fichiers.upload');

    Route::get('/fichiers', Index::class)
        ->name('fichiers.index');

    Route::get('/fichiers/{id}', Show::class)
        ->name('fichiers.show');

    Route::get('/fichiers/{id}/xml', XmlViewer::class)
        ->name('fichiers.xml');

    Route::get('/rejets', RejetsIndex::class)
        ->name('rejets.index');

    Route::get('/stats', StatsIndex::class)
        ->name('stats.index');

    Route::get('/users', UsersIndex::class)
        ->middleware('role:admin')
        ->name('users.index');

    Route::get('/profile', ProfileIndex::class)
        ->name('profile.index');
        
    Route::get('/outils/rib', VerificateurRib::class)
        ->name('outils.rib');
    Route::get('/audit', AuditIndex::class)
        ->middleware('role:admin')
        ->name('audit.index');  

});