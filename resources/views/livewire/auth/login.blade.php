<div>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --btl-green: #0F4A36;
    --btl-green-light: #1A6B4D;
    --btl-green-pale: #4CAF7F;
    --btl-red: #D8232A;
    --btl-red-light: #E84850;
}

.login-root {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    transition: background 0.3s;
    font-family: "DM Sans", "Segoe UI", sans-serif;
    position: relative;
    overflow: hidden;
}
.login-root.dark-mode  {
    background: radial-gradient(ellipse at top, rgba(15,74,54,0.25) 0%, #0a0d14 60%);
}
.login-root.light-mode {
    background: radial-gradient(ellipse at top, rgba(15,74,54,0.08) 0%, #f4f6fb 60%);
}

/* Effet décoratif en arrière-plan */
.login-root::before {
    content: '';
    position: absolute;
    top: -100px;
    right: -100px;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(216,35,42,0.15), transparent 70%);
    border-radius: 50%;
    pointer-events: none;
}
.login-root::after {
    content: '';
    position: absolute;
    bottom: -100px;
    left: -100px;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(15,74,54,0.2), transparent 70%);
    border-radius: 50%;
    pointer-events: none;
}

.login-container {
    width: 100%;
    max-width: 440px;
    position: relative;
    z-index: 1;
}

.login-box {
    width: 100%;
    border-radius: 16px;
    padding: 36px 36px 30px;
    border: 1px solid;
    transition: background 0.3s, border-color 0.3s;
    position: relative;
    overflow: hidden;
    backdrop-filter: blur(20px);
}
.login-box::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--btl-green), var(--btl-red));
}
.dark-mode  .login-box {
    background: rgba(20,25,40,0.85);
    border-color: rgba(255,255,255,0.08);
}
.light-mode .login-box {
    background: rgba(255,255,255,0.95);
    border-color: rgba(0,0,0,0.08);
    box-shadow: 0 24px 64px rgba(15,74,54,0.15);
}

/* Logo BTL réel */
.logo-wrap { text-align: center; margin-bottom: 28px; }
.logo-img-wrapper {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 100px;
    height: 100px;
    background: white;
    border-radius: 20px;
    padding: 12px;
    margin-bottom: 18px;
    box-shadow: 0 12px 32px rgba(15,74,54,0.25), 0 0 0 4px rgba(216,35,42,0.1);
    position: relative;
}
.logo-img-wrapper img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.logo-title {
    font-family: "Syne", sans-serif;
    font-size: 22px;
    font-weight: 700;
    transition: color 0.3s;
    letter-spacing: 0.3px;
}
.dark-mode  .logo-title { color: #f0f4ff; }
.light-mode .logo-title { color: #1a1f2e; }

.logo-sub {
    font-size: 12px;
    margin-top: 6px;
    transition: color 0.3s;
    font-weight: 500;
}
.dark-mode  .logo-sub { color: #8892a4; }
.light-mode .logo-sub { color: #64748b; }

.logo-bank {
    display: inline-block;
    margin-top: 10px;
    padding: 3px 12px;
    background: linear-gradient(135deg, var(--btl-green), var(--btl-green-light));
    color: white;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 1.5px;
    border-radius: 12px;
    text-transform: uppercase;
}

.form-label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 6px;
    letter-spacing: 0.3px;
    transition: color 0.3s;
    text-transform: uppercase;
    font-size: 11px;
    letter-spacing: 0.8px;
}
.dark-mode  .form-label { color: #8892a4; }
.light-mode .form-label { color: #64748b; }

.form-input {
    width: 100%;
    padding: 12px 14px;
    font-size: 14px;
    border-radius: 8px;
    border: 1.5px solid;
    outline: none;
    transition: all 0.2s;
    font-family: inherit;
}
.dark-mode  .form-input {
    background: rgba(15,20,32,0.8);
    border-color: rgba(255,255,255,0.08);
    color: #f0f4ff;
}
.light-mode .form-input {
    background: #f8fafc;
    border-color: rgba(0,0,0,0.08);
    color: #1a1f2e;
}
.form-input:focus {
    border-color: var(--btl-green-pale) !important;
    box-shadow: 0 0 0 3px rgba(15,74,54,0.15);
}

.form-group { margin-bottom: 16px; }

.remember {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 22px;
    font-size: 13px;
    transition: color 0.3s;
}
.dark-mode  .remember { color: #8892a4; }
.light-mode .remember { color: #64748b; }
.remember input { accent-color: var(--btl-green); width: 16px; height: 16px; cursor: pointer; }

.btn-login {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, var(--btl-green), var(--btl-green-light));
    color: white;
    font-weight: 700;
    font-size: 14px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-family: "Syne", sans-serif;
    letter-spacing: 0.5px;
    transition: all 0.2s;
    text-transform: uppercase;
    position: relative;
    overflow: hidden;
}
.btn-login::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 4px;
    height: 100%;
    background: var(--btl-red);
}
.btn-login:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 28px rgba(15,74,54,0.35);
}
.btn-login:active { transform: translateY(0); }
.btn-login:disabled { opacity: 0.7; cursor: wait; }

.toggle-btn {
    position: fixed;
    top: 22px;
    right: 22px;
    width: 42px;
    height: 42px;
    border-radius: 50%;
    border: 2px solid var(--btl-green-pale);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    transition: all 0.2s;
    z-index: 999;
    background: transparent;
    color: var(--btl-green-pale);
}
.toggle-btn:hover {
    border-color: var(--btl-red);
    color: var(--btl-red);
    background: rgba(216,35,42,0.08);
    transform: rotate(15deg);
}

.footer {
    text-align: center;
    margin-top: 24px;
    font-size: 11px;
    transition: color 0.3s;
    font-weight: 500;
}
.footer .footer-brand {
    color: var(--btl-green-pale);
    font-weight: 700;
}
.dark-mode  .footer { color: #4a5568; }
.light-mode .footer { color: #94a3b8; }

.error-box {
    border-radius: 8px;
    padding: 12px 14px;
    margin-bottom: 18px;
    font-size: 13px;
    background: rgba(216,35,42,0.1);
    border: 1.5px solid var(--btl-red);
    color: var(--btl-red);
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
}
</style>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">

<div class="login-root dark-mode" id="login-root">

    <!-- Toggle Dark/Light -->
    <button class="toggle-btn" onclick="toggleMode()" id="toggle-btn" title="Changer le thème">
        <span id="toggle-icon">☀️</span>
    </button>

    <div class="login-container">
        <div class="login-box">
            <div class="logo-wrap">
                <div class="logo-img-wrapper">
                    <img src="{{ asset('img/btl-logo.png') }}" alt="BTL Bank">
                </div>
                <div class="logo-title">Télécompensation</div>
                <div class="logo-sub">Système National SIBTEL</div>
                <div class="logo-bank">Banque Tuniso-Libyenne</div>
            </div>

            @if($errors->any())
            <div class="error-box">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                {{ $errors->first() }}
            </div>
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
                <span wire:loading>Connexion en cours...</span>
            </button>
        </div>

        <div class="footer">
            © 2026 <span class="footer-brand">BTL Bank</span> · Banque Tuniso-Libyenne · Système SIBTEL
        </div>
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