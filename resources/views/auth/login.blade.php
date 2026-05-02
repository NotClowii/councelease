<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — CounselEase</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web@2.0.3"></script>
    <style>
        :root {
            --primary:       #2D6A4F;
            --primary-light: #52B788;
            --primary-dark:  #1B4332;
            --surface:       #F8FAF9;
            --border:        #C8DDD5;
            --text-primary:  #0F2419;
            --text-muted:    #7A9E8E;
            --white:         #FFFFFF;
            --danger:        #E53E3E;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            background: var(--surface);
        }

        /* Left panel */
        .auth-left {
            width: 480px;
            background: linear-gradient(160deg, var(--primary-dark) 0%, #2D6A4F 60%, #40916C 100%);
            padding: 60px 56px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .auth-left::before {
            content: '';
            position: absolute;
            top: -100px; right: -100px;
            width: 400px; height: 400px;
            border-radius: 50%;
            background: rgba(255,255,255,.04);
        }
        .auth-left::after {
            content: '';
            position: absolute;
            bottom: -60px; left: -60px;
            width: 300px; height: 300px;
            border-radius: 50%;
            background: rgba(255,255,255,.03);
        }

        .brand { position: relative; z-index: 1; }
        .brand-icon {
            width: 52px; height: 52px;
            background: rgba(255,255,255,.15);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 20px;
        }
        .brand-icon i { font-size: 26px; color: white; }
        .brand-name { font-size: 28px; font-weight: 700; color: white; letter-spacing: -.5px; }
        .brand-sub  { font-size: 13px; color: rgba(255,255,255,.55); margin-top: 4px; }

        .auth-left-content { position: relative; z-index: 1; }
        .auth-left-content h1 {
            font-family: 'DM Serif Display', serif;
            font-size: 34px;
            color: white;
            line-height: 1.2;
            margin-bottom: 16px;
        }
        .auth-left-content h1 em { color: #74C69D; font-style: italic; }
        .auth-left-content p { font-size: 14px; color: rgba(255,255,255,.6); line-height: 1.7; }

        .features { position: relative; z-index: 1; }
        .feature-item {
            display: flex; align-items: center; gap: 10px;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }
        .feature-item:last-child { border-bottom: none; }
        .feature-item i { font-size: 16px; color: #74C69D; }
        .feature-item span { font-size: 13px; color: rgba(255,255,255,.75); }

        /* Right panel */
        .auth-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }

        .auth-form-wrap {
            width: 100%;
            max-width: 420px;
        }

        .auth-form-wrap h2 { font-size: 24px; font-weight: 700; color: var(--text-primary); margin-bottom: 6px; }
        .auth-form-wrap p  { font-size: 13.5px; color: var(--text-muted); margin-bottom: 32px; }

        .form-group { margin-bottom: 18px; }

        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 7px;
        }

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
            font-size: 17px; color: var(--text-muted);
            pointer-events: none;
        }

        .form-control {
            width: 100%;
            padding: 10px 13px 10px 40px;
            border: 1.5px solid var(--border);
            border-radius: 9px;
            font-size: 14px;
            color: var(--text-primary);
            background: var(--white);
            transition: all .2s;
            font-family: inherit;
            outline: none;
        }
        .form-control:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(82,183,136,.15);
        }
        .form-control.is-invalid { border-color: var(--danger); }

        .field-error { font-size: 12px; color: var(--danger); margin-top: 5px; display: flex; align-items: center; gap: 4px; }

        .pw-toggle {
            position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: var(--text-muted); font-size: 17px;
            padding: 0; transition: color .2s;
        }
        .pw-toggle:hover { color: var(--primary); }

        .remember-row {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 24px;
        }
        .remember-row label { font-size: 13px; font-weight: 400; color: var(--text-muted); margin-bottom: 0; display: flex; align-items: center; gap: 6px; }
        .remember-row a { font-size: 13px; color: var(--primary); font-weight: 500; text-decoration: none; }
        .remember-row a:hover { text-decoration: underline; }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 9px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all .2s;
            font-family: inherit;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-login:hover { background: #245A41; transform: translateY(-1px); box-shadow: 0 4px 14px rgba(45,106,79,.3); }

        .demo-accounts {
            margin-top: 28px;
            padding: 16px;
            background: #F0FFF4;
            border: 1px solid #9AE6B4;
            border-radius: 9px;
        }
        .demo-accounts h4 { font-size: 12px; font-weight: 700; color: #276749; letter-spacing: .4px; text-transform: uppercase; margin-bottom: 10px; }

        .demo-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; }
        .demo-item {
            background: white; border: 1px solid #C6F6D5; border-radius: 7px;
            padding: 8px 10px; cursor: pointer;
            transition: all .2s;
        }
        .demo-item:hover { background: #E6FFED; transform: translateY(-1px); }
        .demo-role { font-size: 11px; font-weight: 700; color: #276749; }
        .demo-cred { font-size: 11px; color: #4A9E6A; margin-top: 2px; }

        .alert {
            padding: 11px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex; align-items: flex-start; gap: 8px;
            border: 1px solid;
        }
        .alert-error { background: #FFF5F5; border-color: #FEB2B2; color: #9B2C2C; }
    </style>
</head>
<body>

<div class="auth-left">
    <div class="brand">
        <div class="brand-icon"><i class="ph-bold ph-heart"></i></div>
        <div class="brand-name">CounselEase</div>
        <div class="brand-sub">Student Guidance Management System</div>
    </div>

    <div class="auth-left-content">
        <h1>Supporting students, <em>one session</em> at a time.</h1>
        <p>A centralized platform for managing student counseling appointments, sessions, and case records — built for guidance offices.</p>
    </div>

    <div class="features">
        <div class="feature-item">
            <i class="ph-bold ph-calendar-check"></i>
            <span>Easy appointment scheduling & approval</span>
        </div>
        <div class="feature-item">
            <i class="ph-bold ph-lock-key"></i>
            <span>Confidential case notes & records</span>
        </div>
        <div class="feature-item">
            <i class="ph-bold ph-chart-bar"></i>
            <span>Reports & counselor workload analytics</span>
        </div>
        <div class="feature-item">
            <i class="ph-bold ph-bell"></i>
            <span>Automated notifications & reminders</span>
        </div>
    </div>
</div>

<div class="auth-right">
    <div class="auth-form-wrap">
        <h2>Welcome back</h2>
        <p>Sign in to your account to continue</p>

        @if(session('success'))
        <div class="alert" style="background:#F0FFF4;border-color:#9AE6B4;color:#276749;">
            <i class="ph-bold ph-check-circle"></i>
            {{ session('success') }}
        </div>
        @endif

        @if($errors->has('email'))
        <div class="alert alert-error">
            <i class="ph-bold ph-x-circle"></i>
            {{ $errors->first('email') }}
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label for="email">Email or Username</label>
                <div class="input-wrap">
                    <i class="ph-bold ph-user input-icon"></i>
                    <input
                        type="text"
                        id="email"
                        name="email"
                        class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                        value="{{ old('email') }}"
                        placeholder="Enter your email or username"
                        autofocus
                        required
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrap">
                    <i class="ph-bold ph-lock input-icon"></i>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        placeholder="Enter your password"
                        required
                    >
                    <button type="button" class="pw-toggle" onclick="togglePw()">
                        <i class="ph-bold ph-eye" id="pwIcon"></i>
                    </button>
                </div>
            </div>

            <div class="remember-row">
                <label>
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    Remember me
                </label>
                <a href="#">Forgot password?</a>
            </div>

            <button type="submit" class="btn-login">
                <i class="ph-bold ph-sign-in"></i>
                Sign In
            </button>
        </form>

        <div class="demo-accounts">
            <h4>🔑 Demo Accounts</h4>
            <div class="demo-grid">
                <div class="demo-item" onclick="fillCreds('admin@counseling.edu','Admin@12345')">
                    <div class="demo-role">System Admin</div>
                    <div class="demo-cred">admin@counseling.edu</div>
                </div>
                <div class="demo-item" onclick="fillCreds('counselor1@counseling.edu','Counsel@12345')">
                    <div class="demo-role">Counselor</div>
                    <div class="demo-cred">counselor1@...</div>
                </div>
                <div class="demo-item" onclick="fillCreds('staff@counseling.edu','Staff@12345')">
                    <div class="demo-role">Office Staff</div>
                    <div class="demo-cred">staff@counseling.edu</div>
                </div>
                <div class="demo-item" onclick="fillCreds('student@counseling.edu','Student@12345')">
                    <div class="demo-role">Student</div>
                    <div class="demo-cred">student@counseling.edu</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePw() {
    const input = document.getElementById('password');
    const icon  = document.getElementById('pwIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'ph-bold ph-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'ph-bold ph-eye';
    }
}

function fillCreds(email, password) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = password;
}
</script>
</body>
</html>
