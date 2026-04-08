<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Log In') ?></title>
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --brand-color: <?= htmlspecialchars($_ENV['SITE_PRIMARY_COLOR'] ?? '#3B82F6') ?>;
        }
    </style>
</head>
<body class="min-h-screen bg-gray-950 flex items-center justify-center px-4">
    <div class="w-full max-w-md">
        <!-- Logo + Site Name -->
        <div class="text-center mb-8">
            <?php if (!empty($_ENV['SITE_LOGO'])): ?>
            <img src="<?= htmlspecialchars($_ENV['SITE_LOGO']) ?>" alt="<?= htmlspecialchars($_ENV['SITE_NAME'] ?? '') ?>" class="h-12 mx-auto mb-4">
            <?php endif; ?>
            <h1 class="text-2xl font-bold text-white"><?= htmlspecialchars($_ENV['SITE_NAME'] ?? 'Welcome') ?></h1>
            <p class="text-gray-400 text-sm mt-1">Sign in to your account</p>
        </div>

        <!-- Error Message -->
        <?php if (!empty($loginError)): ?>
        <div class="mb-4 p-3 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 text-sm text-center">
            <?= htmlspecialchars($loginError) ?>
        </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form id="login-form" class="bg-gray-900 border border-gray-800 rounded-2xl p-6 space-y-4">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-300 mb-1">Email</label>
                <input type="email" id="email" name="email" required autocomplete="email"
                    class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-300 mb-1">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password"
                    class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            </div>

            <button type="submit" id="login-btn"
                class="w-full py-3 font-semibold rounded-xl text-white transition-colors"
                style="background-color: var(--brand-color);">
                Sign In
            </button>

            <div id="login-status" class="hidden text-sm text-center"></div>
        </form>

        <?php if (!empty($_ENV['GOOGLE_CLIENT_ID'])): ?>
        <!-- Google SSO -->
        <div class="mt-4">
            <div class="relative my-4">
                <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-800"></div></div>
                <div class="relative flex justify-center text-xs"><span class="px-3 bg-gray-950 text-gray-500">or</span></div>
            </div>
            <a href="/api/auth/google-login.php" class="flex items-center justify-center gap-3 w-full py-3 bg-gray-900 border border-gray-800 rounded-xl text-gray-300 hover:bg-gray-800 hover:text-white transition-colors text-sm font-medium">
                <svg class="w-5 h-5" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                Continue with Google
            </a>
        </div>
        <?php endif; ?>

        <!-- No HipHop.World branding for independent sites -->
    </div>

    <script>
    document.getElementById('login-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('login-btn');
        const status = document.getElementById('login-status');
        btn.disabled = true;
        btn.textContent = 'Signing in...';
        status.className = 'hidden';

        try {
            const res = await fetch('/api/member/login.php', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({
                    email: document.getElementById('email').value,
                    password: document.getElementById('password').value
                })
            });
            const data = await res.json();

            if (data.success) {
                status.textContent = 'Success! Redirecting...';
                status.className = 'text-sm text-center text-green-400';
                window.location.href = data.redirect || '/members';
            } else {
                status.textContent = data.error || 'Login failed';
                status.className = 'text-sm text-center text-red-400';
                btn.disabled = false;
                btn.textContent = 'Sign In';
            }
        } catch (err) {
            status.textContent = 'Network error. Please try again.';
            status.className = 'text-sm text-center text-red-400';
            btn.disabled = false;
            btn.textContent = 'Sign In';
        }
    });
    </script>
</body>
</html>
