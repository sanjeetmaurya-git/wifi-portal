{{-- resources/views/partials/header.blade.php --}}
<header class="main-header">
    <div class="header-container">
        {{-- LEFT SECTION: Branding & Status --}}
        <div class="user-info">
            <div class="status-dot"></div>
            <div class="brand-details">
                <h3 class="brand-name">PM-WANI.Typeone</h3>
                <span class="user-mobile">{{ session('mobile') ?? 'Authenticated' }}</span>
            </div>
        </div>

        {{-- RIGHT SECTION: Desktop Navigation --}}
        <div class="desktop-nav">
            <nav class="nav-links">
                <a href="{{ url('/usage') }}" class="nav-link">Usage</a>
                <a href="{{ url('/plans') }}" class="nav-link">Plans</a>
                <form action="{{ route('logout') }}" method="POST" class="logout-form">
                    @csrf
                    <button type="submit" class="nav-link logout-btn">Logout</button>
                </form>
            </nav>
            <div class="profile-avatar">
                <div class="avatar-wrapper">
                    <div class="avatar-gradient">
                        <div class="avatar-inner">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(session('user_name') ?? 'User') }}&background=00d4aa&color=fff&bold=true" alt="Avatar">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- MOBILE TOGGLE --}}
        <button class="mobile-toggle" id="mobileToggle">
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
        </button>
    </div>

    {{-- MOBILE DRAWER --}}
    <div class="mobile-overlay" id="mobileOverlay"></div>
    <div class="mobile-drawer" id="mobileDrawer">
        <div class="drawer-header">
            <button class="close-drawer" id="closeDrawer">✕</button>
        </div>

        <div class="drawer-profile">
            <div class="drawer-avatar">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(session('user_name') ?? 'User') }}&background=00d4aa&color=fff&bold=true&size=100" alt="Avatar">
            </div>
            <div class="drawer-user-info">
                <h4>{{ session('user_name') ?? 'Guest User' }}</h4>
                <span>{{ session('mobile') ?? 'WiFi User' }}</span>
            </div>
        </div>

        <nav class="drawer-nav">
            <a href="{{ url('/usage') }}" class="drawer-nav-link"><span class="nav-icon">📊</span> Usage Dashboard</a>
            <a href="{{ url('/plans') }}" class="drawer-nav-link"><span class="nav-icon">💳</span> Buy Plans</a>
            <form action="{{ route('logout') }}" method="POST" class="drawer-logout-form">
                @csrf
                <button type="submit" class="drawer-nav-link logout-drawer-btn">
                    <span class="nav-icon">🚪</span> Logout Session
                </button>
            </form>
        </nav>
    </div>
</header>

<style>
    .main-header {
        position: sticky; top: 0; z-index: 1000;
        background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(14px);
        border-bottom: 1px solid rgba(255, 255, 255, 0.08); box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        font-family: 'Outfit', sans-serif;
    }
    .header-container { max-width: 1400px; margin: 0 auto; padding: 10px 15px; display: flex; justify-content: space-between; align-items: center; }
    .status-dot { width: 10px; height: 10px; background: #00d4aa; border-radius: 50%; box-shadow: 0 0 10px #00d4aa; animation: glow 2s infinite; }
    @keyframes glow { 0%, 100% { opacity: 0.6; } 50% { opacity: 1; } }
    .brand-name { font-size: 1.1rem; font-weight: 700; color: #fff; margin: 0; letter-spacing: -0.5px; }
    .user-mobile { font-size: 0.75rem; color: #94a3b8; }
    .desktop-nav { display: flex; align-items: center; gap: 20px; }
    .nav-links { display: flex; gap: 20px; align-items: center; }
    .nav-link { color: #e2e8f0; text-decoration: none; font-size: 0.9rem; font-weight: 500; transition: 0.3s; }
    .nav-link:hover { color: #00d4aa; }
    .logout-btn { background: none; border: none; cursor: pointer; padding: 0; font-family: inherit; }
    .avatar-wrapper { width: 38px; height: 38px; border-radius: 50%; padding: 2px; background: linear-gradient(to right, #00d4aa, #6366f1); }
    .avatar-inner { width: 100%; height: 100%; border-radius: 50%; overflow: hidden; background: #1e293b; }
    .avatar-inner img { width: 100%; height: 100%; object-fit: cover; }
    
    /* Mobile Styles */
    .mobile-toggle { display: none; flex-direction: column; gap: 5px; background: none; border: none; cursor: pointer; }
    .hamburger-line { width: 24px; height: 2px; background: #fff; border-radius: 2px; }
    .mobile-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.8); backdrop-filter: blur(4px); opacity: 0; visibility: hidden; transition: 0.3s; z-index: 1050; }
    .mobile-drawer { position: fixed; top: 0; right: -300px; width: 280px; height: 100%; background: #0f172a; z-index: 1100; transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: -10px 0 30px rgba(0,0,0,0.5); padding: 20px; }
    .mobile-drawer.open { right: 0; }
    .mobile-overlay.active { opacity: 1; visibility: visible; }
    .drawer-profile { text-align: center; margin-bottom: 30px; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 20px; }
    .drawer-avatar { width: 70px; height: 70px; border-radius: 50%; margin: 0 auto 10px; border: 2px solid #00d4aa; padding: 3px; }
    .drawer-avatar img { width: 100%; height: 100%; border-radius: 50%; }
    .drawer-nav { display: flex; flex-direction: column; gap: 10px; }
    .drawer-nav-link { display: flex; align-items: center; gap: 12px; padding: 12px; color: #fff; text-decoration: none; border-radius: 10px; background: rgba(255,255,255,0.03); }
    .logout-drawer-btn { width: 100%; border: none; cursor: pointer; color: #f87171; background: rgba(239, 68, 68, 0.05); }

    @media (max-width: 768px) {
        .desktop-nav { display: none; }
        .mobile-toggle { display: flex; }
    }
</style>

<script>
    (function() {
        const toggle = document.getElementById('mobileToggle');
        const drawer = document.getElementById('mobileDrawer');
        const overlay = document.getElementById('mobileOverlay');
        const close = document.getElementById('closeDrawer');

        toggle?.addEventListener('click', () => {
            drawer.classList.add('open');
            overlay.classList.add('active');
        });

        [close, overlay].forEach(el => el?.addEventListener('click', () => {
            drawer.classList.remove('open');
            overlay.classList.remove('active');
        }));
    })();
</script>