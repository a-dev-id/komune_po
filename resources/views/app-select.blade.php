{{-- resources/views/app-select.blade.php --}}
<!doctype html>
<html lang="en" data-theme="dark">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Choose an app</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://nandinibali.com/images/favicon-njhg.png" type="image/x-icon" rel="icon">

    <style>
        :root {
            --shadow: 0 30px 80px rgba(0, 0, 0, .55);
            --radius: 18px;
        }

        /* DARK (default) */
        html[data-theme="dark"] {
            --bg0: #050608;
            --bg1: #0b0d12;

            --card: #0f131a;
            --card2: #0b0f15;

            --line: rgba(255, 255, 255, .08);

            --text: rgba(255, 255, 255, .88);
            --muted: rgba(255, 255, 255, .62);
            --muted2: rgba(255, 255, 255, .45);

            --pill-bg: rgba(0, 0, 0, .25);
            --pill-border: rgba(255, 255, 255, .10);

            --btn-text: rgba(255, 255, 255, .90);
            --btn-bg: rgba(0, 0, 0, .25);
            --btn-border: rgba(255, 255, 255, .14);

            --toggleBg: rgba(0, 0, 0, .25);
            --toggleLine: rgba(255, 255, 255, .14);

            --brand: #ff2d20;
            --gold: #a67c3d;
            --emerald: #059669;
            --emerald-dark: #047857;
        }

        /* LIGHT */
        html[data-theme="light"] {
            --bg0: #f7f7fb;
            --bg1: #eef0f7;

            --card: rgba(255, 255, 255, .92);
            --card2: rgba(255, 255, 255, .82);

            --line: rgba(11, 13, 18, .10);

            --text: #0b0d12;
            --muted: rgba(11, 13, 18, .65);
            --muted2: rgba(11, 13, 18, .45);

            --pill-bg: rgba(11, 13, 18, .06);
            --pill-border: rgba(11, 13, 18, .12);

            --btn-text: rgba(11, 13, 18, .90);
            --btn-bg: rgba(255, 255, 255, .70);
            --btn-border: rgba(11, 13, 18, .12);

            --toggleBg: rgba(255, 255, 255, .70);
            --toggleLine: rgba(11, 13, 18, .12);

            --brand: #ff2d20;
            --gold: #a67c3d;
            --emerald: #059669;
            --emerald-dark: #047857;

            --shadow: 0 22px 70px rgba(0, 0, 0, .18);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            color: var(--text);
            min-height: 100vh;
            display: grid;
            place-items: center;
            background:
                radial-gradient(1100px 600px at 50% 35%, rgba(255, 45, 32, .12), transparent 55%),
                radial-gradient(900px 500px at 30% 75%, rgba(166, 124, 61, .12), transparent 55%),
                linear-gradient(180deg, var(--bg0), var(--bg1));
            padding: 28px;
            transition: background .2s ease, color .2s ease;
        }

        .shell {
            width: min(1366px, 100%);
        }

        .top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 18px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .dot {
            width: 12px;
            height: 12px;
            border-radius: 999px;
            background: linear-gradient(180deg, #20ff3b, #60ffa0);
            box-shadow: 0 0 0 6px rgb(32 255 49 / 12%);
        }

        .title {
            font-weight: 700;
            letter-spacing: -.02em;
            font-size: 18px;
            margin: 0;
            line-height: 1.2;
            color: var(--text);
        }

        .subtitle {
            margin: 0;
            color: var(--muted);
            font-size: 13px;
        }

        .panel {
            position: relative;
            border: 1px solid var(--line);
            background: linear-gradient(180deg, rgba(255, 255, 255, .02), rgba(255, 255, 255, .01));
            border-radius: calc(var(--radius) + 6px);
            box-shadow: var(--shadow);
            padding: 18px;
            padding-top: 64px;
        }

        .theme {
            position: absolute;
            top: 18px;
            right: 18px;
            z-index: 10;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 14px;
        }

        @media (min-width: 820px) {
            .panel {
                padding-top: 72px;
            }

            .theme {
                top: 14px;
                right: 14px;
            }
        }

        @media (min-width: 1180px) {
            .grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }

        .card {
            position: relative;
            overflow: hidden;
            border-radius: var(--radius);
            border: 1px solid var(--line);
            background:
                radial-gradient(600px 240px at 20% 0%, rgba(255, 255, 255, .06), transparent 55%),
                linear-gradient(180deg, rgba(255, 255, 255, .02), rgba(255, 255, 255, .01));
            min-height: 240px;
            padding: 18px 18px 16px;
            transition: transform .18s ease, border-color .18s ease, background .18s ease;
        }

        html[data-theme="light"] .card {
            background:
                radial-gradient(600px 240px at 20% 0%, rgba(255, 255, 255, .8), transparent 55%),
                linear-gradient(180deg, #ffffff, #f5f6fa);
        }

        .card:hover {
            transform: translateY(-2px);
            border-color: rgba(255, 255, 255, .14);
        }

        html[data-theme="light"] .card:hover {
            border-color: rgba(11, 13, 18, .18);
        }

        .card::after {
            content: "";
            position: absolute;
            inset: -1px;
            background:
                radial-gradient(500px 260px at 95% 20%, rgba(255, 45, 32, .14), transparent 60%),
                radial-gradient(520px 300px at 15% 85%, rgba(166, 124, 61, .14), transparent 60%);
            opacity: .8;
            pointer-events: none;
            mix-blend-mode: screen;
        }

        html[data-theme="light"] .card::after {
            opacity: .35;
            mix-blend-mode: multiply;
        }

        .card-inner {
            position: relative;
            z-index: 2;
        }

        .kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: var(--muted);
            padding: 6px 10px;
            border-radius: 999px;
            border: 1px solid var(--pill-border);
            background: var(--pill-bg);
            margin-bottom: 12px;
        }

        .icon {
            width: 18px;
            height: 18px;
            display: inline-grid;
            place-items: center;
            border-radius: 6px;
            background: rgba(255, 255, 255, .06);
            border: 1px solid rgba(255, 255, 255, .10);
        }

        html[data-theme="light"] .icon {
            background: rgba(11, 13, 18, .06);
            border-color: rgba(11, 13, 18, .10);
        }

        .h {
            margin: 0 0 6px;
            font-size: 22px;
            letter-spacing: -.02em;
            color: var(--text);
        }

        .p {
            margin: 0 0 14px;
            color: var(--muted);
            font-size: 13.5px;
            line-height: 1.45;
            max-width: 52ch;
        }

        .actions {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: auto;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            border-radius: 12px;
            padding: 10px 12px;
            font-weight: 600;
            font-size: 13px;
            text-decoration: none;
            border: 1px solid var(--btn-border);
            color: var(--btn-text);
            background: var(--btn-bg);
            transition: transform .15s ease, background .15s ease, border-color .15s ease;
            width: fit-content;
        }

        .btn:hover {
            transform: translateY(-1px);
            background: rgba(0, 0, 0, .35);
        }

        html[data-theme="light"] .btn:hover {
            background: rgba(255, 255, 255, .95);
        }

        .btn-primary {
            color: #fff;
            background: linear-gradient(180deg, #ff5a4f, #ff2d20);
            border-color: #ff2d20;
        }

        .btn-gold {
            color: #5c3a00;
            background: linear-gradient(180deg, #f0d6a8, #e3c28a);
            border-color: #d8b16a;
        }

        .btn-blue {
            color: #fff;
            background: linear-gradient(180deg, #3b82f6, #1d4ed8);
            border-color: rgba(59, 130, 246, .55);
        }

        .btn-green {
            color: #fff;
            background: linear-gradient(180deg, var(--emerald), var(--emerald-dark));
            border-color: rgba(5, 150, 105, .55);
        }

        .meta {
            margin-top: 10px;
            display: flex;
            gap: 14px;
            color: var(--muted2);
            font-size: 12px;
            flex-wrap: wrap;
        }

        .meta span {
            display: inline-flex;
            gap: 8px;
            align-items: center;
        }

        .dot-sm {
            width: 6px;
            height: 6px;
            border-radius: 999px;
            background: var(--muted2);
        }

        .footer {
            margin-top: 14px;
            color: var(--muted2);
            font-size: 12px;
            display: flex;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .link {
            color: var(--muted);
            text-decoration: none;
            border-bottom: 1px dashed rgba(255, 255, 255, .18);
        }

        html[data-theme="light"] .link {
            border-bottom-color: rgba(11, 13, 18, .18);
        }

        .link:hover {
            color: var(--text);
            border-bottom-color: rgba(255, 255, 255, .35);
        }

        html[data-theme="light"] .link:hover {
            border-bottom-color: rgba(11, 13, 18, .30);
        }

        .theme-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            border-radius: 12px;
            padding: 10px 12px;
            font-weight: 600;
            font-size: 13px;
            border: 1px solid var(--toggleLine);
            background: var(--toggleBg);
            color: var(--text);
            cursor: pointer;
            transition: transform .15s ease, background .15s ease, border-color .15s ease;
        }

        .theme-btn:hover {
            transform: translateY(-1px);
        }

        .theme-ico {
            width: 18px;
            height: 18px;
            display: inline-grid;
            place-items: center;
        }

        .theme-text {
            color: var(--muted);
        }

        .logo {
            height: 44px;
            width: auto;
            display: block;
        }

        @media (max-width: 640px) {
            .logo {
                height: 36px;
            }
        }

    </style>
</head>

<body>
    <div class="shell">
        <div class="top">
            <div class="brand">
                <img src="https://nandinibali.com/images/logo-njhg.png" alt="Nandini Jungle by Hanging Gardens" class="logo" />

                <div>
                    <p class="title">Nandini Jungle — Apps</p>
                    <p class="subtitle">Select a module to continue</p>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="theme">
                <button id="themeToggle" class="theme-btn" type="button" aria-label="Toggle theme">
                    <span class="theme-ico" id="themeIcon" aria-hidden="true">🌙</span>
                    <span class="theme-text" id="themeText">Dark</span>
                </button>
            </div>

            <div class="grid">
                {{-- Newsletter --}}
                <section class="card" aria-label="Newsletter">
                    <div class="card-inner">
                        <div class="kicker">
                            <span class="icon">✉️</span>
                            Marketing
                        </div>

                        <h2 class="h">Newsletter</h2>
                        <p class="p">
                            Create, schedule, and send newsletters. Track deliveries, opens, clicks, and performance by campaign.
                        </p>

                        <div class="actions">
                            <a class="btn btn-primary" href="https://nandiniapps.cloud/newsletter" target="_blank">
                                Open Newsletter
                                <span aria-hidden="true">→</span>
                            </a>
                        </div>

                        <div class="meta">
                            <span><span class="dot-sm"></span> Campaigns</span>
                            <span><span class="dot-sm"></span> Subscribers</span>
                            <span><span class="dot-sm"></span> Tracking</span>
                        </div>
                    </div>
                </section>

                {{-- Guest Letter --}}
                <section class="card" aria-label="Guest Letter">
                    <div class="card-inner">
                        <div class="kicker">
                            <span class="icon">🧾</span>
                            Operations
                        </div>

                        <h2 class="h">Guest Letter</h2>
                        <p class="p">
                            Manage confirmation, pre-arrival, and post-stay messages. Keep guest communications consistent and timely.
                        </p>

                        <div class="actions">
                            <a class="btn btn-gold" href="https://nandiniapps.cloud/guestletter" target="_blank">
                                Open Guest Letter
                                <span aria-hidden="true">→</span>
                            </a>
                        </div>

                        <div class="meta">
                            <span><span class="dot-sm"></span> Confirmation</span>
                            <span><span class="dot-sm"></span> Pre-arrival</span>
                            <span><span class="dot-sm"></span> Post-stay</span>
                        </div>
                    </div>
                </section>

                {{-- Purchasing --}}
                <section class="card" aria-label="Purchasing">
                    <div class="card-inner">
                        <div class="kicker">
                            <span class="icon">🧱</span>
                            Finance & Procurement
                        </div>

                        <h2 class="h">Purchasing</h2>
                        <p class="p">
                            Create Purchase Requests (PR), collect vendor comparisons, and issue Purchase Orders (PO) with approval tracking.
                        </p>

                        <div class="actions">
                            <a class="btn btn-blue" href="https://v4.nandiniapps.cloud/purchasing-lite" target="_blank">
                                Open Purchasing
                                <span aria-hidden="true">→</span>
                            </a>
                        </div>

                        <div class="meta">
                            <span><span class="dot-sm"></span> Purchase Requests</span>
                            <span><span class="dot-sm"></span> Vendor Quotes</span>
                            <span><span class="dot-sm"></span> Purchase Orders</span>
                        </div>
                    </div>
                </section>

                {{-- Sales --}}
                <section class="card" aria-label="Sales">
                    <div class="card-inner">
                        <div class="kicker">
                            <span class="icon">🤝</span>
                            Sales
                        </div>

                        <h2 class="h">Travel Agent</h2>
                        <p class="p">
                            Manage travel agent accounts, contracts, agent access codes, and contract notification emails from one place.
                        </p>

                        <div class="actions">
                            <a class="btn btn-green" href="https://v2.nandiniapps.cloud/agent" target="_blank">
                                Open Sales
                                <span aria-hidden="true">→</span>
                            </a>
                        </div>

                        <div class="meta">
                            <span><span class="dot-sm"></span> Agents</span>
                            <span><span class="dot-sm"></span> Contracts</span>
                            <span><span class="dot-sm"></span> Notifications</span>
                        </div>
                    </div>
                </section>
            </div>

            <div class="footer">
                <span>nandiniapps.cloud</span>
                <span>
                    <a class="link" href="/" rel="nofollow">Home</a>
                </span>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const key = 'njhg_theme';
            const html = document.documentElement;

            function getSystemTheme() {
                return window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches
                    ? 'light'
                    : 'dark';
            }

            function applyTheme(theme) {
                html.setAttribute('data-theme', theme);

                const icon = document.getElementById('themeIcon');
                const text = document.getElementById('themeText');

                if (icon) icon.textContent = theme === 'light' ? '☀️' : '🌙';
                if (text) text.textContent = theme === 'light' ? 'Light' : 'Dark';
            }

            const saved = localStorage.getItem(key);
            applyTheme(saved || getSystemTheme());

            const btn = document.getElementById('themeToggle');
            if (btn) {
                btn.addEventListener('click', () => {
                    const current = html.getAttribute('data-theme') || 'dark';
                    const next = current === 'dark' ? 'light' : 'dark';
                    localStorage.setItem(key, next);
                    applyTheme(next);
                });
            }
        })();
    </script>
</body>

</html>