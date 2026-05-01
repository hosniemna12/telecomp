<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Auth\Login;
use App\Livewire\Fichiers\Upload;
use App\Livewire\Fichiers\Index;
use App\Livewire\Fichiers\Show;
use App\Livewire\Fichiers\XmlViewer;
use App\Livewire\Rejets\Index as RejetsIndex;
use App\Livewire\Rejets\Pacs004Generator;
use App\Livewire\Users\Index as UsersIndex;
use App\Livewire\Stats\Index as StatsIndex;
use App\Livewire\Profile\Index as ProfileIndex;
use App\Livewire\Outils\VerificateurRib;
use App\Livewire\Profile\Show as ProfileShow;
use App\Livewire\Audit\Index as AuditIndex;

Route::get('/', fn() => redirect()->route('login'));
Route::get('/login', Login::class)->name('login');
Route::post('/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect()->route('login');
})->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {

    // Dashboard — tous les roles
    Route::get('/dashboard', App\Livewire\Dashboard::class)
        ->name('dashboard');

    // ── Fichiers ──────────────────────────────────────────────────
    // Upload : admin, operateur, superviseur (lecteur = lecture seule)
    Route::get('/fichiers/upload', Upload::class)
        ->middleware('role:admin,operateur,superviseur')
        ->name('fichiers.upload');

    // Liste & detail : tous les roles
    Route::get('/fichiers', Index::class)
        ->name('fichiers.index');

    Route::get('/fichiers/{id}', Show::class)
        ->name('fichiers.show');

    Route::get('/fichiers/{id}/xml', XmlViewer::class)
        ->name('fichiers.xml');

    // ── Rejets & Pacs.004 ─────────────────────────────────────────
    // pacs004 AVANT rejets index pour eviter conflit
    Route::get('/rejets/pacs004', Pacs004Generator::class)
        ->middleware('role:admin,operateur,superviseur')
        ->name('rejets.pacs004');

    Route::get('/rejets', RejetsIndex::class)
        ->middleware('role:admin,operateur,superviseur')
        ->name('rejets.index');

    Route::get('/pacs004/{id}/telecharger', function ($id) {
        $pacs004 = \App\Models\TcPacs004::findOrFail($id);
        return response()->streamDownload(
            fn() => print($pacs004->contenu_xml),
            'pacs004_' . $pacs004->msg_id . '.xml',
            ['Content-Type' => 'application/xml']
        );
    })->name('pacs004.telecharger');

    // ── Stats : admin, superviseur, lecteur (pas operateur)
    Route::get('/stats', StatsIndex::class)
        ->middleware('role:admin,superviseur,lecteur')
        ->name('stats.index');

    // ── Utilisateurs : admin uniquement
    Route::get('/users', UsersIndex::class)
        ->middleware('role:admin')
        ->name('users.index');

    // ── Profil : tous
    Route::get('/profile', ProfileIndex::class)
        ->name('profile.index');

    // ── Outils : tous
    Route::get('/outils/rib', VerificateurRib::class)
        ->name('outils.rib');

    // ── Audit : admin uniquement
    Route::get('/audit', AuditIndex::class)
        ->middleware('role:admin')
        ->name('audit.index');

    // Rapports Export
    Route::get('/rapport', [App\Http\Controllers\RapportController::class, 'index'])
        ->middleware('role:admin,superviseur')
        ->name('rapport.index');

    Route::get('/rapport/generer', [App\Http\Controllers\RapportController::class, 'generer'])
        ->middleware('role:admin,superviseur')
        ->name('rapport.generer');
});
