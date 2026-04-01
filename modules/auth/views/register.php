<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Choose Role - <?= e(config('app.name')) ?></title>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link
            href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
            rel="stylesheet"
        />
        <script src="https://unpkg.com/lucide@latest" defer></script>
        <style>
            :root {
                --bg: #f4f4f4;
                --text: #1f1f1f;
                --muted: #666;
                --card-radius: 14px;
                --yellow: #f6c40e;
            }
            * {
                box-sizing: border-box;
            }
            body {
                margin: 0;
                font-family: "Plus Jakarta Sans", sans-serif;
                background: var(--bg);
                color: var(--text);
            }
            .page {
                min-height: 100vh;
                display: flex;
                align-items: flex-start;
                justify-content: center;
                padding: 90px 24px 40px;
            }
            .wrapper {
                width: 100%;
                max-width: 1080px;
            }
            h1 {
                text-align: center;
                margin: 0 0 8px;
                font-size: clamp(2rem, 3vw, 2.55rem);
            }
            .sub {
                text-align: center;
                margin: 0 0 48px;
                color: var(--muted);
                font-size: 1.1rem;
            }
            .flash {
                max-width: 760px;
                margin: 0 auto 24px;
                border-radius: 10px;
                padding: 12px 14px;
                font-size: 0.9rem;
                text-align: center;
            }
            .flash-error {
                background: #fee2e2;
                border: 1px solid #fecaca;
                color: #991b1b;
            }
            .cards {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 48px;
            }
            .card {
                text-align: center;
            }
            .icon-wrap {
                width: 92px;
                height: 92px;
                border-radius: 999px;
                background: #e7e7e7;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 16px;
            }
            .icon-wrap svg {
                width: 42px;
                height: 42px;
                stroke-width: 1.75;
            }
            .card h2 {
                margin: 0 0 6px;
                font-size: 2rem;
                font-weight: 700;
            }
            .card p {
                margin: 0 auto 18px;
                color: var(--muted);
                line-height: 1.45;
                max-width: 290px;
                min-height: 64px;
            }
            .signup-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 100%;
                max-width: 230px;
                height: 52px;
                border-radius: 999px;
                text-decoration: none;
                background: var(--yellow);
                color: #111;
                font-weight: 700;
                transition: filter 0.15s ease;
            }
            .signup-btn:hover {
                filter: brightness(0.96);
            }
            .login-note {
                margin-top: 28px;
                text-align: center;
                color: var(--muted);
            }
            .login-note a {
                color: #111;
                font-weight: 600;
            }
            @media (max-width: 980px) {
                .cards {
                    grid-template-columns: 1fr;
                    gap: 24px;
                    max-width: 420px;
                    margin: 0 auto;
                }
                .card p {
                    min-height: 0;
                }
            }
        </style>
    </head>
    <body>
        <main class="page">
            <div class="wrapper">
                <h1>Choose your role</h1>
                <p class="sub">Select the role that best describes you to get started.</p>

                <?php if ($error = get_flash('error')): ?>
                    <div class="flash flash-error"><?= e($error) ?></div>
                <?php endif; ?>

                <section class="cards" aria-label="Role options">
                    <article class="card">
                        <div class="icon-wrap"><i data-lucide="user-round"></i></div>
                        <h2>General User</h2>
                        <p>Individuals seeking assistance or resources during a disaster.</p>
                        <a class="signup-btn" href="/register/general">Sign Up</a>
                    </article>

                    <article class="card">
                        <div class="icon-wrap"><i data-lucide="building-2"></i></div>
                        <h2>NGO</h2>
                        <p>Organizations providing aid and support to affected communities.</p>
                        <a class="signup-btn" href="/register/ngo">Sign Up</a>
                    </article>

                    <article class="card">
                        <div class="icon-wrap"><i data-lucide="users-round"></i></div>
                        <h2>Volunteer</h2>
                        <p>Individuals offering their time and skills to support relief efforts.</p>
                        <a class="signup-btn" href="/register/volunteer">Sign Up</a>
                    </article>
                </section>

                <p class="login-note">Already have an account? <a href="/login">Log in</a></p>
            </div>
        </main>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                if (window.lucide) {
                    window.lucide.createIcons();
                }
            });
        </script>
    </body>
</html>
