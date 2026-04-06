<?php

namespace App\Livewire\Profile;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\User;  // Adjust to the appropriate model for profile data

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    // Properties for profile component (adjust as needed)
    public string $recherche = '';
    public bool $showDetails = false;

    protected $queryString = [
        'recherche' => ['except' => ''],
    ];

    public function updatingRecherche(): void { $this->resetPage(); }

    public function toggleDetails(): void
    {
        $this->showDetails = !$this->showDetails;
    }

    public function render()
    {
        // Example: Display current user's profile or list users if admin
        $user = \Illuminate\Support\Facades\Auth::user();

        return view('livewire.profile.index', compact('user'));
    }
}