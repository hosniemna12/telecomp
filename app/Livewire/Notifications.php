<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\TcNotification;
use Illuminate\Support\Facades\Auth;

class Notifications extends Component
{
    public bool $showPanel = false;
    public int  $count     = 0;

    public function mount(): void
    {
        $this->refreshCount();
    }

    public function togglePanel(): void
    {
        $this->showPanel = !$this->showPanel;
        if ($this->showPanel) {
            $this->refreshCount();
        }
    }

    public function marquerLu(int $id): void
    {
        TcNotification::where('id', $id)
            ->where('user_id', Auth::id())
            ->update(['lu' => 1]);

        $this->refreshCount();
    }

    public function toutMarquerLu(): void
    {
        TcNotification::where('user_id', Auth::id())
            ->where('lu', 0)
            ->update(['lu' => 1]);

        $this->refreshCount();
    }

    private function refreshCount(): void
    {
        $this->count = TcNotification::where('user_id', Auth::id())
            ->where('lu', 0)
            ->count();
    }

    public function render()
    {
        $notifications = TcNotification::with('fichier')
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        return view('livewire.notifications', compact('notifications'));
    }
}
