<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>BE Store — Ingresar</title>
    @vite(['resources/css/app.css'])
    <style>
        body { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #F0F7FF 0%, #E8F0F8 100%); }
        .login-wrap { width: 100%; max-width: 380px; padding: 1rem; }
        .login-card { background: #fff; border-radius: 16px; padding: 2rem; box-shadow: 0 8px 32px rgba(44,62,80,.12); border: 1px solid var(--bs-gray-border); }
        .login-logo { text-align: center; margin-bottom: 1.5rem; }
        .login-logo .big { font-size: 2rem; font-weight: 700; color: var(--bs-black); }
        .login-logo .big span { color: var(--bs-blue); }
        .login-logo .sub { font-size: .8rem; color: var(--bs-muted); margin-top: .2rem; }
        .login-error { background: #FFF0F0; border: 1px solid #FECACA; border-radius: 8px; padding: .65rem .85rem; font-size: .82rem; color: #C0392B; margin-bottom: 1rem; display: flex; align-items: flex-start; gap: .4rem; }
        .captcha-wrap { display: flex; align-items: center; gap: .75rem; margin-bottom: .75rem; }
        .captcha-img  { border-radius: 8px; cursor: pointer; border: 1px solid var(--bs-gray-border); }
        .captcha-refresh { background: none; border: none; color: var(--bs-muted); cursor: pointer; font-size: .8rem; text-decoration: underline; padding: 0; }
        .login-btn { width: 100%; padding: .7rem; background: var(--bs-black); color: #fff; border: none; border-radius: 8px; font-size: .9rem; font-weight: 600; cursor: pointer; margin-top: .25rem; transition: background .15s; }
        .login-btn:hover { background: #3D5166; }
        .login-footer { text-align: center; font-size: .72rem; color: var(--bs-muted); margin-top: 1rem; }
    </style>
</head>
<body>
<div class="login-wrap">
    <div class="login-card">
        <div class="login-logo">
            <div class="big">BE <span>Store</span></div>
            <div class="sub">Sistema de gestión comercial</div>
        </div>

        {{-- Errores específicos --}}
        @if($errors->any())
        <div class="login-error">
            <span>⚠</span>
            <div>
                @if($errors->has('email') && str_contains($errors->first('email'), 'credenciales'))
                    El email o la contraseña son incorrectos.
                @elseif($errors->has('email'))
                    {{ $errors->first('email') }}
                @elseif($errors->has('captcha'))
                    El código de verificación es incorrecto. Intentá de nuevo.
                @else
                    {{ $errors->first() }}
                @endif
            </div>
        </div>
        @endif

        @if(session('status'))
        <div style="background:#E8F8F0;border:1px solid #A9DFBF;border-radius:8px;padding:.65rem .85rem;font-size:.82rem;color:#2E7D5E;margin-bottom:1rem">
            {{ session('status') }}
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="bs-form-group">
                <label class="bs-label">Correo electrónico</label>
                <input class="bs-input" type="email" name="email"
                       value="{{ old('email') }}" required autofocus
                       placeholder="admin@bestore.com"
                       style="{{ $errors->has('email') ? 'border-color:#C0392B' : '' }}"/>
            </div>

            <div class="bs-form-group">
                <label class="bs-label">Contraseña</label>
                <div style="position:relative">
                    <input class="bs-input" type="password" name="password" id="pwd-field"
                           required placeholder="••••••••"
                           style="{{ $errors->has('password') ? 'border-color:#C0392B' : '' }}"/>
                    <button type="button" onclick="togglePwd()"
                            style="position:absolute;right:.6rem;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--bs-muted);cursor:pointer;font-size:.9rem">
                        👁
                    </button>
                </div>
            </div>

            {{-- CAPTCHA --}}
            <div class="bs-form-group">
                <label class="bs-label">Verificación de seguridad</label>
                <div class="captcha-wrap">
                    <img src="{{ captcha_src() }}" id="captcha-img" class="captcha-img"
                         onclick="refreshCaptcha()" title="Clic para cambiar" height="44"/>
                    <button type="button" class="captcha-refresh" onclick="refreshCaptcha()">
                        🔄 Cambiar imagen
                    </button>
                </div>
                <input class="bs-input" type="text" name="captcha"
                       placeholder="Ingresá los caracteres de la imagen"
                       autocomplete="off"
                       style="{{ $errors->has('captcha') ? 'border-color:#C0392B' : '' }}"/>
                @if($errors->has('captcha'))
                <span style="font-size:.75rem;color:#C0392B">{{ $errors->first('captcha') }}</span>
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
function refreshCaptcha() {
    const img = document.getElementById('captcha-img');
    img.src = '/captcha/default?' + Math.random();
}
function togglePwd() {
    const f = document.getElementById('pwd-field');
    f.type = f.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>
