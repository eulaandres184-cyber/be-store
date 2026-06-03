<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>BE Store — Ingresar</title>
    @vite(['resources/css/app.css'])
    <style>
        body { min-height:100vh; display:flex; align-items:center; justify-content:center; background:linear-gradient(135deg,#F0F7FF 0%,#E8F0F8 100%); }
        .login-wrap { width:100%; max-width:400px; padding:1rem; }
        .login-card { background:#fff; border-radius:16px; padding:2rem; box-shadow:0 8px 32px rgba(44,62,80,.12); border:1px solid var(--bs-gray-border); }
        .login-logo { text-align:center; margin-bottom:1.5rem; }
        .login-logo .big { font-size:2rem; font-weight:700; color:var(--bs-black); }
        .login-logo .big span { color:var(--bs-blue); }
        .login-logo .sub { font-size:.8rem; color:var(--bs-muted); margin-top:.2rem; }
        .login-error { background:#FFF0F0; border:1px solid #FECACA; border-radius:8px; padding:.65rem .85rem; font-size:.82rem; color:#C0392B; margin-bottom:1rem; display:flex; gap:.4rem; }
        .login-btn { width:100%; padding:.7rem; background:var(--bs-black); color:#fff; border:none; border-radius:8px; font-size:.9rem; font-weight:600; cursor:pointer; margin-top:.25rem; transition:background .15s; }
        .login-btn:hover { background:#3D5166; }
        .pwd-wrap { position:relative; }
        .pwd-toggle { position:absolute; right:.6rem; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--bs-muted); cursor:pointer; font-size:.9rem; }
        .login-footer { text-align:center; font-size:.72rem; color:var(--bs-muted); margin-top:1rem; }
        .field-error { font-size:.75rem; color:#C0392B; margin-top:.2rem; display:block; }
    </style>
</head>
<body>
<div class="login-wrap">
    <div class="login-card">
        <div class="login-logo">
            <div class="big">BE <span>Store</span></div>
            <div class="sub">Sistema de gestión comercial</div>
        </div>

        @if(session('status'))
        <div style="background:#E8F8F0;border:1px solid #A9DFBF;border-radius:8px;padding:.65rem;font-size:.82rem;color:#2E7D5E;margin-bottom:1rem">
            {{ session('status') }}
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            {{-- Email --}}
            <div class="bs-form-group">
                <label class="bs-label">Correo electrónico</label>
                <input class="bs-input" type="email" name="email"
                       value="{{ old('email') }}" required autofocus
                       placeholder="admin@bestore.com"
                       style="{{ $errors->has('email') ? 'border-color:#C0392B' : '' }}"/>
                @if($errors->has('email'))
                    <span class="field-error">
                        @if(str_contains(strtolower($errors->first('email')), 'credenciales') || str_contains(strtolower($errors->first('email')), 'failed'))
                            Email o contraseña incorrectos.
                        @elseif(str_contains(strtolower($errors->first('email')), 'intentos') || str_contains(strtolower($errors->first('email')), 'segundos'))
                            {{ $errors->first('email') }}
                        @else
                            Ingresá un correo electrónico válido.
                        @endif
                    </span>
                @endif
            </div>

            {{-- Contraseña --}}
            <div class="bs-form-group">
                <label class="bs-label">Contraseña</label>
                <div class="pwd-wrap">
                    <input class="bs-input" type="password" name="password"
                           id="pwd-field" required placeholder="Mínimo 6 caracteres"
                           minlength="6"
                           style="{{ $errors->has('password') ? 'border-color:#C0392B' : '' }}"/>
                    <button type="button" class="pwd-toggle" id="pwd-toggle">👁</button>
                </div>
                @if($errors->has('password'))
                    <span class="field-error">La contraseña debe tener al menos 6 caracteres.</span>
                @endif
            </div>

            <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.75rem">
                <input type="checkbox" name="remember" id="remember" style="accent-color:var(--bs-blue)">
                <label for="remember" style="font-size:.8rem;color:var(--bs-muted);cursor:pointer">Mantener sesión iniciada</label>
            </div>

            <button type="submit" class="login-btn">Ingresar al sistema</button>
        </form>

        <div class="login-footer">Solo usuarios autorizados por BE Store</div>
    </div>
</div>
<script>
document.getElementById('pwd-toggle').addEventListener('click', function() {
    var f = document.getElementById('pwd-field');
    f.type = f.type === 'password' ? 'text' : 'password';
    this.textContent = f.type === 'password' ? '👁' : '🙈';
});
</script>
</body>
</html>
