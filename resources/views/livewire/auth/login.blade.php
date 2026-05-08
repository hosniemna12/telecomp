<div>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --gold: #1A5F47;
    --gold-l: #4CAF7F;
    --red-accent: #C41E3A;
    --bg-dark: #0a0d14;
    --bg-dark-card: #141928;
    --bg-dark-input: #0f1420;
    --border-dark: rgba(255,255,255,0.08);
    --text-dark-1: #f0f4ff;
    --text-dark-2: #8892a4;
    --text-dark-3: #4a5568;
    --bg-light: #f4f6fb;
    --bg-light-card: #ffffff;
    --bg-light-input: #f8fafc;
    --border-light: rgba(0,0,0,0.1);
    --text-light-1: #1a1f2e;
    --text-light-2: #4a5568;
    --text-light-3: #94a3b8;
}

.login-root {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    transition: background 0.3s;
    font-family: "DM Sans", "Segoe UI", sans-serif;
}
.dark-mode  { background: var(--bg-dark); }
.light-mode { background: var(--bg-light); }

.login-box {
    width: 100%;
    max-width: 400px;
    border-radius: 16px;
    padding: 36px 32px;
    border: 1px solid;
    transition: background 0.3s, border-color 0.3s;
    position: relative;
    overflow: hidden;
}
.login-box::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--gold), transparent, var(--red-accent));
}
.dark-mode  .login-box { background: var(--bg-dark-card); border-color: var(--border-dark); }
.light-mode .login-box { background: var(--bg-light-card); border-color: var(--border-light); box-shadow: 0 8px 40px rgba(0,0,0,0.08); }

.logo-wrap { text-align: center; margin-bottom: 28px; }
.logo-icon {
    width: 56px; height: 56px;
    background: linear-gradient(135deg, var(--gold), var(--gold-l));
    border-radius: 14px;
    display: inline-flex; align-items: center; justify-content: center;
    font-weight: 800; font-size: 18px; color: white;
    margin-bottom: 14px;
    font-family: "Syne", sans-serif;
    box-shadow: 0 8px 24px rgba(26,95,71,0.3);
    position: relative;
}
.logo-icon::after {
    content: '';
    position: absolute;
    top: -2px;
    right: -2px;
    width: 12px;
    height: 12px;
    background: var(--red-accent);
    border-radius: 50%;
    border: 2px solid white;
}
.logo-title {
    font-family: "Syne", sans-serif;
    font-size: 22px; font-weight: 700;
    transition: color 0.3s;
}
.dark-mode  .logo-title { color: var(--text-dark-1); }
.light-mode .logo-title { color: var(--text-light-1); }
.logo-sub { font-size: 12px; margin-top: 4px; transition: color 0.3s; }
.dark-mode  .logo-sub { color: var(--text-dark-3); }
.light-mode .logo-sub { color: var(--text-light-3); }

.form-label { display: block; font-size: 12px; font-weight: 500; margin-bottom: 6px; letter-spacing: 0.3px; transition: color 0.3s; }
.dark-mode  .form-label { color: var(--text-dark-2); }
.light-mode .form-label { color: var(--text-light-2); }

.form-input {
    width: 100%; padding: 10px 14px;
    font-size: 14px; border-radius: 8px;
    border: 1.5px solid; outline: none;
    transition: all 0.2s;
    font-family: inherit;
}
.dark-mode  .form-input { background: var(--bg-dark-input); border-color: var(--border-dark); color: var(--text-dark-1); }
.light-mode .form-input { background: var(--bg-light-input); border-color: var(--border-light); color: var(--text-light-1); }
.form-input:focus { border-color: var(--gold) !important; }

.form-group { margin-bottom: 16px; }

.remember { display: flex; align-items: center; gap: 8px; margin-bottom: 22px; font-size: 13px; transition: color 0.3s; }
.dark-mode  .remember { color: var(--text-dark-2); }
.light-mode .remember { color: var(--text-light-2); }
.remember input { accent-color: var(--gold); width: 15px; height: 15px; }

.btn-login {
    width: 100%; padding: 12px;
    background: linear-gradient(135deg, var(--gold), var(--gold-l));
    color: white; font-weight: 700; font-size: 14px;
    border: none; border-radius: 8px; cursor: pointer;
    font-family: "Syne", sans-serif;
    letter-spacing: 0.3px; transition: opacity 0.15s, transform 0.15s, box-shadow 0.15s;
}
.btn-login:hover { opacity: 0.95; transform: translateY(-2px); box-shadow: 0 8px 24px rgba(26,95,71,0.3); }

.toggle-btn {
    position: fixed; top: 18px; right: 18px;
    width: 42px; height: 42px; border-radius: 50%;
    border: 2px solid var(--gold); cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; transition: all 0.2s; z-index: 999;
    background: transparent;
    color: var(--gold);
}
.toggle-btn:hover { border-color: var(--red-accent); color: var(--red-accent); background: rgba(196,30,58,0.1); }

.footer { text-align: center; margin-top: 20px; font-size: 11px; transition: color 0.3s; }
.dark-mode  .footer { color: var(--text-dark-3); }
.light-mode .footer { color: var(--text-light-3); }

.error-box {
    border-radius: 8px; padding: 12px 14px;
    margin-bottom: 18px; font-size: 13px;
    background: rgba(196,30,58,0.1);
    border: 1.5px solid var(--red-accent);
    color: var(--red-accent);
    font-weight: 500;
}
</style>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">

<div class="login-root dark-mode" id="login-root">

    <!-- Toggle Dark/Light -->
    <button class="toggle-btn" onclick="toggleMode()" id="toggle-btn" title="Changer le thème">
        <span id="toggle-icon">☀️</span>
    </button>

    <div>
        <div class="login-box">
            <div class="logo-wrap">
                <div class="logo-icon">BTL</div>
                <div class="logo-title">Télécompensation</div>
                <div class="logo-sub">Système National SIBTEL — Banque Tuniso-Libyenne</div>
            </div>

            @if($errors->any())
            <div class="error-box">{{ $errors->first() }}</div>
            @endif

            <div class="form-group">
                <label class="form-label">Adresse email</label>
                <input wire:model="email" type="email" class="form-input" placeholder="votre@btl.com.tn" autocomplete="email">
            </div>

            <div class="form-group">
                <label class="form-label">Mot de passe</label>
                <input wire:model="password" type="password" class="form-input" placeholder="••••••••" autocomplete="current-password">
            </div>

            <div class="remember">
                <input wire:model="remember" type="checkbox" id="rem">
                <label for="rem" style="cursor:pointer">Se souvenir de moi</label>
            </div>

            <button wire:click="connecter" wire:loading.attr="disabled" class="btn-login">
                <span wire:loading.remove>Se connecter</span>
                <span wire:loading>Connexion...</span>
            </button>
        </div>

        <div class="footer">© 2026 BTL — Banque Tuniso-Libyenne · Système SIBTEL</div>
    </div>
</div>

<script>
function toggleMode() {
    const root = document.getElementById("login-root");
    const icon = document.getElementById("toggle-icon");
    const isDark = root.classList.contains("dark-mode");
    root.classList.toggle("dark-mode", !isDark);
    root.classList.toggle("light-mode", isDark);
    icon.textContent = isDark ? "🌙" : "☀️";
    localStorage.setItem("btl-theme", isDark ? "light" : "dark");
}
const saved = localStorage.getItem("btl-theme");
if (saved === "light") {
    document.getElementById("login-root").classList.replace("dark-mode","light-mode");
    document.getElementById("toggle-icon").textContent = "🌙";
}
</script>
</div>