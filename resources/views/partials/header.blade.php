<header style="background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(255,255,255,0.1); padding: 15px 25px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 1000; font-family: 'Outfit', sans-serif;">
    <!-- Left: User Info -->
    <div style="display: flex; align-items: center; gap: 12px;">
        <div style="width: 10px; height: 10px; background: #00d4aa; border-radius: 50%; box-shadow: 0 0 10px #00d4aa;"></div>
        <div>
            <h3 style="margin: 0; font-size: 16px; color: white; font-weight: 600;">{{ session('user_name') ?? 'Guest User' }}</h3>
            <span style="font-size: 12px; color: rgba(255,255,255,0.5);">{{ session('mobile') }}</span>
        </div>
    </div>

    <!-- Right: Profile Action -->
    <a href="/profile" style="text-decoration: none; position: relative;">
        <div style="width: 42px; height: 42px; border-radius: 50%; background: linear-gradient(45deg, #00d4aa, #0072ff); padding: 2px;">
            <div style="width: 100%; height: 100%; border-radius: 50%; background: #1a1a2e; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(session('user_name') ?? 'U') }}&background=random" alt="User" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
        </div>
    </a>
</header>
