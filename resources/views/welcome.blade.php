<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>PMWANI.Typeone | Blazing Fast & Affordable Internet </title>
    <meta name="description"
        content="Select a daily, unlimited, or data pack plan to get blazing-fast internet access. Experience cheaper, high-speed internet with PM-WANI compliant plans. Daily, unlimited, and data packs. Seamless connectivity for all.">
    <meta name="keywords"
        content="PM-WANI, cheap internet, wifi plans, unlimited data, public wifi, affordable broadband">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg: #cbcbd6ff;
            --card-bg: rgba(20, 20, 35, 0.7);
            --glass-border: rgba(255, 255, 255, 0.08);
            --text-primary: #f0f3fa;
            --text-muted: #9ca3af;
            --green: #22c55e;
            --cyan: #06b6d4;
            --amber: #f59e0b;
            --indigo: #6366f1;
            --purple: #a855f7;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: var(--bg);
            font-family: 'Outfit', sans-serif;
            color: var(--text-primary);
            min-height: 100vh;
            /* overflow-x: hidden; */
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            z-index: 0;
            background: radial-gradient(circle at 20% 30%, rgba(99, 102, 241, 0.15), transparent 70%),
                radial-gradient(circle at 85% 70%, rgba(6, 182, 212, 0.12), transparent 70%);
            pointer-events: none;
            inset: 0;
            z-index: -2;
        }

        /* SLIDER SECTION */
        .hero-slider {
            position: relative;
            max-width: 1400px;
            margin: 2rem auto;
            border-radius: 32px;
            overflow: hidden;
            box-shadow: 0 25px 40px -12px rgba(0, 0, 0, 0.4);
            z-index: 2;
        }

        .slider-container {
            position: relative;
            width: 100%;
            height: 460px;
        }

        .slide {
            position: absolute;
            inset: 0;
            opacity: 0;
            transition: opacity 0.7s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            backdrop-filter: brightness(0.7);
        }

        .slide.active {
            opacity: 1;
            z-index: 2;
        }

        .slide::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.65), rgba(0, 0, 0, 0.4));
            z-index: 0;
        }

        .slide-content {
            position: relative;
            z-index: 3;
            max-width: 750px;
            padding: 2rem;
            animation: fadeUp 0.8s ease;
        }

        .slide-content h2 {
            font-size: 2.4rem;
            font-weight: 800;
            background: linear-gradient(135deg, #fff, #86efac);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin-bottom: 1rem;
        }

        .slide-content p {
            font-size: 1.1rem;
            color: #e2e8f0;
            margin-bottom: 1.5rem;
        }

        .slide-btn {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 0.7rem 2rem;
            border-radius: 40px;
            font-weight: 600;
            color: white;
            display: inline-block;
            transition: 0.2s;
            text-decoration: none;
        }

        .slide-btn:hover {
            background: var(--cyan);
            border-color: var(--cyan);
            transform: scale(1.02);
        }

        .slider-nav {
            position: absolute;
            bottom: 20px;
            left: 0;
            right: 0;
            display: flex;
            justify-content: center;
            gap: 12px;
            z-index: 10;
        }

        .dot {
            width: 12px;
            height: 12px;
            background: rgba(255, 255, 255, 0.4);
            border-radius: 50%;
            cursor: pointer;
            transition: 0.2s;
        }

        .dot.active,
        .dot:hover {
            background: var(--cyan);
            transform: scale(1.2);
        }

        .prev,
        .next {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.5);
            border: none;
            color: white;
            font-size: 1.8rem;
            padding: 6px 14px;
            border-radius: 50px;
            cursor: pointer;
            z-index: 12;
            backdrop-filter: blur(4px);
            transition: 0.2s;
        }

        .prev {
            left: 20px;
        }

        .next {
            right: 20px;
        }

        .prev:hover,
        .next:hover {
            background: var(--cyan);
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .slider-container {
                height: 380px;
            }

            .slide-content h2 {
                font-size: 1.8rem;
            }

            .slide-content p {
                font-size: 0.9rem;
            }
        }

        /* services section */
        .section-title {
            text-align: center;
            font-size: 2rem;
            font-weight: 700;
            margin: 3rem 0 1.2rem;
        }

        .section-title i {
            color: var(--cyan);
            margin-right: 8px;
        }

        .services-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 1.8rem;
            max-width: 1300px;
            margin: 0 auto;
            padding: 1rem;
        }

        .service-card {
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            border: 1px solid var(--glass-border);
            border-radius: 28px;
            padding: 1.8rem;
            width: 280px;
            transition: all 0.3s ease;
            text-align: center;
        }

        .service-card:hover {
            transform: translateY(-8px);
            border-color: rgba(6, 182, 212, 0.5);
            box-shadow: 0 20px 35px -12px rgba(6, 182, 212, 0.2);
        }

        .service-icon {
            font-size: 2.5rem;
            background: linear-gradient(145deg, #22c55e, #06b6d4);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin-bottom: 1rem;
        }

        .service-card h3 {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 0.6rem;
        }

        .service-card p {
            color: var(--text-muted);
            font-size: 0.85rem;
            margin-bottom: 1.2rem;
        }

        .cart-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.15);
            padding: 0.5rem 1.2rem;
            border-radius: 60px;
            font-weight: 600;
            font-size: 0.8rem;
            cursor: pointer;
            transition: 0.2s;
            color: #e2e8f0;
        }

        .cart-btn i {
            margin-right: 6px;
        }

        .cart-btn:hover {
            background: var(--cyan);
            color: #0a0a1a;
            border-color: var(--cyan);
        }

        /* plans section (original upgraded) */
        .plans-header {
            margin-top: 3rem;
        }

        .tab-bar {
            display: flex;
            justify-content: center;
            gap: 0.8rem;
            padding: 1rem;
            flex-wrap: wrap;
        }

        .tab-btn {
            padding: 0.6rem 1.5rem;
            border-radius: 60px;
            border: 1px solid var(--glass-border);
            background: transparent;
            color: var(--text-muted);
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }

        .tab-btn.active,
        .tab-btn:hover {
            border-color: var(--indigo);
            background: rgba(99, 102, 241, 0.2);
            color: white;
        }

        .tab-btn[data-type="daily"].active {
            border-color: var(--green);
            background: rgba(34, 197, 94, 0.15);
        }

        .tab-btn[data-type="unlimited"].active {
            border-color: var(--cyan);
            background: rgba(6, 182, 212, 0.15);
        }

        .tab-btn[data-type="datapack"].active {
            border-color: var(--amber);
            background: rgba(245, 158, 11, 0.15);
        }

        .plans-section {
            max-width: 1300px;
            margin: 0 auto;
            padding: 1rem;
        }

        .plan-section {
            margin-bottom: 2rem;
        }

        .plans-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 1.8rem;
        }

        .plan-card {
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            border: 1px solid var(--glass-border);
            border-radius: 28px;
            padding: 1.8rem;
            width: 280px;
            transition: transform 0.25s, box-shadow 0.25s;
            position: relative;
        }

        .plan-card:hover {
            transform: translateY(-12px) scale(1.02);
            border-color: rgba(255, 255, 255, 0.2);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .plan-card::after {
            content: '';
            position: absolute;
            inset: -1px;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            border-radius: 28px;
            z-index: -1;
            opacity: 0;
            transition: 0.3s;
        }

        .plan-card:hover::after {
            opacity: 1;
        }

        @keyframes pulse-slow {
            0% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.05);
                opacity: 0.8;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .badge.bestseller {
            animation: pulse-slow 2s infinite;
        }

        .badge {
            position: absolute;
            top: -12px;
            right: 20px;
            background: var(--green);
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 0.7rem;
            font-weight: 800;
        }

        .plan-name {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .plan-price {
            font-size: 20px;
            font-weight: 800;
            margin: 1rem 0;
        }

        .plan-price span {
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .plan-features {
            list-style: none;
            margin: 1rem 0;
            border-top: 1px solid var(--glass-border);
            padding-top: 1rem;
        }

        .plan-features li {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
            color: #cdd6f4;
        }

        .btn-buy {
            width: 100%;
            padding: 0.8rem;
            border-radius: 50px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: 0.2s;
            font-family: 'Outfit', sans-serif;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .btn-buy.daily {
            background: linear-gradient(95deg, #22c55e, #15803d);
            color: white;
        }

        .btn-buy.unlimited {
            background: linear-gradient(95deg, #06b6d4, #0e7490);
            color: white;
        }

        .btn-buy.datapack {
            background: linear-gradient(95deg, #f59e0b, #b45309);
            color: white;
        }

        .btn-buy:hover {
            filter: brightness(1.1);
            transform: scale(0.98);
        }

        .btn-buy::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: 0.5s;
            z-index: -1;
        }

        .btn-buy:hover::before {
            left: 100%;
        }

        footer {
            text-align: center;
            padding: 2rem;
            color: var(--text-muted);
            border-top: 1px solid var(--glass-border);
            margin-top: 2rem;
            font-size: 0.8rem;
        }

        @media (max-width: 640px) {

            .service-card,
            .plan-card {
                width: 100%;
                max-width: 340px;
            }

            .section-title {
                font-size: 1.6rem;
            }
        }

        .toast-msg {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            background: #1e293b;
            backdrop-filter: blur(12px);
            padding: 10px 24px;
            border-radius: 60px;
            color: #a5f3fc;
            z-index: 999;
            font-weight: 500;
            font-size: 0.85rem;
            border-left: 4px solid var(--cyan);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            transition: 0.2s;
        }

        i.fa,
        i.far,
        i.fas {
            pointer-events: none;
        }
    </style>
</head>

<body>
    @include('partials.header')

    <!-- SLIDER with 3 images & PM-WANI SEO content -->

    <div class="hero-slider">
        <div class="slider-container" id="sliderContainer">
            <div class="slide active" style="background-image: url('uploads/PM_WANI.png');">
                <div class="slide-content">
                    <h2>PM-WANI: Cheaper Internet for Bharat</h2>
                    <p>Government-backed public Wi-Fi hotspots delivering ultra-affordable data. High speed, no
                        middlemen, truly democratized internet.</p>
                    <a href="#plans" class="slide-btn">Explore Plans <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>

            <div class="slide active" style="background-image: url('uploads/PM_WANI.png');">
                <div class="slide-content">
                    <h2>Unlimited Potential, Minimal Cost</h2>
                    <p>Daily, monthly, and data packs starting as low as ₹19/day. PM-WANI revolution makes internet
                        access 10x cheaper.</p>
                    <a href="#plans" class="slide-btn">View Deals <i class="fas fa-tags"></i></a>
                </div>
            </div>

            <div class="slide active" style="background-image: url('uploads/PM_WANI.png');">
                <div class="slide-content">
                    <h2>Seamless Roaming · No Hidden Fees</h2>
                    <p>India's first affordable carrier-grade WiFi under PM-WANI framework. Connect anywhere, pay only
                        for what you use.</p>
                    <a href="#services" class="slide-btn">Discover Services <i class="fas fa-wifi"></i></a>
                </div>
            </div>

            <button class="prev" id="prevSlide"><i class="fas fa-chevron-left"></i></button>
            <button class="next" id="nextSlide"><i class="fas fa-chevron-right"></i></button>
            <div class="slider-nav" id="dotsContainer"></div>
        </div>
    </div>

    <!-- SERVICES SECTION with cart animation -->
    <div id="services" class="section-title">
        <i class="fas fa-concierge-bell"></i> Our Digital Services
    </div>

    <div class="services-grid">
        <div class="service-card">
            <div class="service-icon"><i class="fas fa-tachometer-alt"></i></div>
            <h3>HyperFast Hotspot</h3>
            <p>Up to 100 Mbps in PM-WANI zones, ideal for streaming & work from home.</p>
            <button class="cart-btn" data-service="HyperFast Hotspot"><i class="fas fa-cart-plus"></i> Add to
                Cart</button>
        </div>

        <div class="service-card">
            <div class="service-icon"><i class="fas fa-shield-alt"></i></div>
            <h3>Secure VPN Add-on</h3>
            <p>Encrypted tunnels, privacy protection for all your browsing sessions.</p>
            <button class="cart-btn" data-service="Secure VPN Add-on"><i class="fas fa-cart-plus"></i> Add to
                Cart</button>
        </div>

        <div class="service-card">
            <div class="service-icon"><i class="fas fa-headset"></i></div>
            <h3>24/7 Expert Support</h3>
            <p>Priority resolution for connectivity & hardware issues. Free for plan holders.</p>
            <button class="cart-btn" data-service="24/7 Expert Support"><i class="fas fa-cart-plus"></i> Add to
                Cart</button>
        </div>

        <div class="service-card">
            <div class="service-icon"><i class="fas fa-charging-station"></i></div>
            <h3>PM-WANI Kiosk Setup</h3>
            <p>Become a PDO agent, earn revenue by offering public WiFi hotspots.</p>
            <button class="cart-btn" data-service="PM-WANI Kiosk Setup"><i class="fas fa-cart-plus"></i> Add to
                Cart</button>
        </div>

        <div class="service-card">
            <div class="service-icon"><i class="fas fa-globe"></i></div>
            <h3>Pan-India Roaming</h3>
            <p>Use your data pack across 5000+ hotspots without extra charges.</p>
            <button class="cart-btn" data-service="Pan-India Roaming"><i class="fas fa-cart-plus"></i> Add to
                Cart</button>
        </div>

        <div class="service-card">
            <div class="service-icon"><i class="fas fa-chart-line"></i></div>
            <h3>Smart Analytics</h3>
            <p>Track usage, speed tests, and data insights in real-time dashboard.</p>
            <button class="cart-btn" data-service="Smart Analytics"><i class="fas fa-cart-plus"></i> Add to
                Cart</button>
        </div>
    </div>

    <!-- PLANS SECTION (filterable & animated) -->
    <div id="plans" class="section-title">
        <i class="fas fa-tags"></i> Choose Your WiFi Plan
    </div>

    <div class="tab-bar">
        <button class="tab-btn active" data-type="all" onclick="filterPlans('all')">🔥 All Plans</button>
        <button class="tab-btn" data-type="daily" onclick="filterPlans('daily')">📆 Daily Packs</button>
        <button class="tab-btn" data-type="unlimited" onclick="filterPlans('unlimited')">♾️ Unlimited</button>
        <button class="tab-btn" data-type="datapack" onclick="filterPlans('datapack')">📦 Data Packs</button>
    </div>

    <div class="plans-section" id="plansSection">
        <!-- Daily Plans Section -->
        <div class="plan-section" data-type="daily" id="dailySection">
            <div class="plans-grid">
                @forelse($plans->where('plan_type', 'daily') as $plan)
                    <div class="plan-card daily">
                        @if($loop->first)
                            <div class="badge bestseller" style="background: #22c55e;">Bestseller</div>
                        @endif
                        <div class="plan-name">{{ $plan->name }}</div>
                        <div class="plan-price">
                            @if(empty($plan->price) || $plan->price <= 0)
                                Free <span>/
                                    {{ str_replace([' Days', ' Day', ' Hours', ' Hour', ' Mins', ' Min'], ['d', 'd', 'hr', 'hr', 'm', 'm'], $plan->validity_label) }}</span>
                            @else
                                ₹{{ (float) $plan->price }} <span>/
                                    {{ str_replace([' Days', ' Day', ' Hours', ' Hour', ' Mins', ' Min'], ['d', 'd', 'hr', 'hr', 'm', 'm'], $plan->validity_label) }}</span>
                            @endif
                        </div>
                        <ul class="plan-features">
                            <li><i class="fas fa-check-circle" style="color:#22c55e;"></i> {{ $plan->data_label }}
                                High-speed data</li>
                            <li><i class="fas fa-check-circle" style="color:#22c55e;"></i> {{ $plan->validity_label }}
                                validity</li>
                            <li><i class="fas fa-check-circle" style="color:#22c55e;"></i> Speed:
                                {{ $plan->download_limit ?? '100 Mbps' }}
                            </li>
                            <li><i class="fas fa-check-circle" style="color:#22c55e;"></i> PM-WANI access</li>
                        </ul>
                        <a href="{{ route('login') }}" class="btn-buy daily"
                            style="display:inline-block; text-align:center; text-decoration:none;"
                            data-plan="{{ $plan->name }} - ₹{{ (float) $plan->price }}">Buy Now <i
                                class="fas fa-bolt"></i></a>
                    </div>
                @empty
                    <p style="color:var(--text-muted); text-align:center;">No daily plans available at the moment.</p>
                @endforelse
            </div>
        </div>

        <!-- Unlimited Plans Section -->
        <div class="plan-section" data-type="unlimited" id="unlimitedSection" style="display: none;">
            <div class="plans-grid">
                @forelse($plans->where('plan_type', 'unlimited') as $plan)
                    <div class="plan-card unlimited">
                        @if($loop->first)
                            <div class="badge bestseller" style="background: #06b6d4;">Unlimited</div>
                        @endif
                        <div class="plan-name">{{ $plan->name }}</div>
                        <div class="plan-price">
                            @if(empty($plan->price) || $plan->price <= 0)
                                Free <span>/
                                    {{ str_replace([' Days', ' Day', ' Hours', ' Hour', ' Mins', ' Min'], ['d', 'd', 'hr', 'hr', 'm', 'm'], $plan->validity_label) }}</span>
                            @else
                                ₹{{ (float) $plan->price }} <span>/
                                    {{ str_replace([' Days', ' Day', ' Hours', ' Hour', ' Mins', ' Min'], ['d', 'd', 'hr', 'hr', 'm', 'm'], $plan->validity_label) }}</span>
                            @endif
                        </div>
                        <ul class="plan-features">
                            <li><i class="fas fa-infinity" style="color:#06b6d4;"></i> Speed:
                                {{ $plan->download_limit ?? 'High Speed' }}
                            </li>
                            <li><i class="fas fa-check-circle" style="color:#06b6d4;"></i> Truly unlimited</li>
                            <li><i class="fas fa-check-circle" style="color:#06b6d4;"></i> {{ $plan->validity_label }}
                                validity</li>
                            <li><i class="fas fa-check-circle" style="color:#06b6d4;"></i> Free installation</li>
                        </ul>
                        <a href="{{ route('login') }}" class="btn-buy unlimited"
                            style="display:inline-block; text-align:center; text-decoration:none;"
                            data-plan="{{ $plan->name }} - ₹{{ (float) $plan->price }}">Activate Unlimited</a>
                    </div>
                @empty
                    <p style="color:var(--text-muted); text-align:center;">No unlimited plans available at the moment.</p>
                @endforelse
            </div>
        </div>

        <!-- Data Pack Section -->
        <div class="plan-section" data-type="datapack" id="datapackSection" style="display: none;">
            <div class="plans-grid">
                @forelse($plans->where('plan_type', 'datapack') as $plan)
                    <div class="plan-card datapack">
                        @if($loop->first)
                            <div class="badge bestseller" style="background: #f59e0b;">Validity
                                {{ str_replace([' Days', ' Day', ' Hours', ' Hour', ' Mins', ' Min'], ['d', 'd', 'hr', 'hr', 'm', 'm'], $plan->validity_label) }}
                            </div>
                        @endif
                        <div class="plan-name">{{ $plan->name }}</div>
                        <div class="plan-price">
                            @if(empty($plan->price) || $plan->price <= 0)
                                Free <span>/
                                    {{ str_replace([' Days', ' Day', ' Hours', ' Hour', ' Mins', ' Min'], ['d', 'd', 'hr', 'hr', 'm', 'm'], $plan->validity_label) }}</span>
                            @else
                                ₹{{ (float) $plan->price }} <span>/
                                    {{ str_replace([' Days', ' Day', ' Hours', ' Hour', ' Mins', ' Min'], ['d', 'd', 'hr', 'hr', 'm', 'm'], $plan->validity_label) }}</span>
                            @endif
                        </div>
                        <ul class="plan-features">
                            <li><i class="fas fa-database" style="color:#f59e0b;"></i> {{ $plan->data_label ?? 'Data' }}
                                total data</li>
                            <li><i class="fas fa-check-circle" style="color:#f59e0b;"></i> Carry forward unused</li>
                            <li><i class="fas fa-check-circle" style="color:#f59e0b;"></i> {{ $plan->validity_label }}
                                validity</li>
                            <li><i class="fas fa-check-circle" style="color:#f59e0b;"></i> Access all hotspots</li>
                        </ul>
                        <a href="{{ route('login') }}" class="btn-buy datapack"
                            style="display:inline-block; text-align:center; text-decoration:none;"
                            data-plan="{{ $plan->name }} - ₹{{ (float) $plan->price }}">Buy Data Pack</a>
                    </div>
                @empty
                    <p style="color:var(--text-muted); text-align:center;">No data packs available at the moment.</p>
                @endforelse
            </div>
        </div>
    </div>

    <footer>
        <i class="fas fa-shield-alt"></i> PM-WANI compliant | Made in India | Affordable Internet for All • 24x7 support
        • <strong>#DigitalIndia</strong>
    </footer>

    <script>
        // ---------- SLIDER WITH AUTO ROTATE ----------

        let slides = document.querySelectorAll('.slide');
        let currentSlide = 0;
        let slideInterval;
        const prevBtn = document.getElementById('prevSlide');
        const nextBtn = document.getElementById('nextSlide');
        const dotsContainer = document.getElementById('dotsContainer');

        function updateSlides(index) {
            slides.forEach((slide, i) => {
                slide.classList.toggle('active', i === index);
            });
            document.querySelectorAll('.dot').forEach((dot, i) => {
                dot.classList.toggle('active', i === index);
            });
            currentSlide = index;
        }

        function createDots() {
            dotsContainer.innerHTML = '';
            slides.forEach((_, i) => {
                const dot = document.createElement('div');
                dot.classList.add('dot');
                if (i === currentSlide) dot.classList.add('active');
                dot.addEventListener('click', () => {
                    clearInterval(slideInterval);
                    updateSlides(i);
                    startAutoSlide();
                });
                dotsContainer.appendChild(dot);
            });
        }

        function nextSlide() { updateSlides((currentSlide + 1) % slides.length); }
        function prevSlide() { updateSlides((currentSlide - 1 + slides.length) % slides.length); }
        function startAutoSlide() {
            if (slideInterval) clearInterval(slideInterval);
            slideInterval = setInterval(() => { nextSlide(); }, 5000);
        }

        prevBtn.addEventListener('click', () => { clearInterval(slideInterval); prevSlide(); startAutoSlide(); });
        nextBtn.addEventListener('click', () => { clearInterval(slideInterval); nextSlide(); startAutoSlide(); });
        createDots();
        startAutoSlide();

        // ---------- PLAN FILTER ----------

        window.filterPlans = function (type) {
            // update tabs active style
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
                if (btn.getAttribute('data-type') === type) btn.classList.add('active');
                if (type === 'all' && btn.getAttribute('data-type') === 'all') btn.classList.add('active');
            });
            const dailySec = document.getElementById('dailySection');
            const unlimitedSec = document.getElementById('unlimitedSection');
            const datapackSec = document.getElementById('datapackSection');
            if (type === 'all') {
                dailySec.style.display = 'block';
                unlimitedSec.style.display = 'block';
                datapackSec.style.display = 'block';
            } else if (type === 'daily') {
                dailySec.style.display = 'block';
                unlimitedSec.style.display = 'none';
                datapackSec.style.display = 'none';
            } else if (type === 'unlimited') {
                dailySec.style.display = 'none';
                unlimitedSec.style.display = 'block';
                datapackSec.style.display = 'none';
            } else if (type === 'datapack') {
                dailySec.style.display = 'none';
                unlimitedSec.style.display = 'none';
                datapackSec.style.display = 'block';
            }
        };

        // initial show all

        filterPlans('all');

        // Toast message helper

        function showToast(message, isPlan = false) {
            let toastDiv = document.querySelector('.toast-msg');
            if (toastDiv) toastDiv.remove();
            let toast = document.createElement('div');
            toast.className = 'toast-msg';
            toast.innerHTML = `<i class="fas ${isPlan ? 'fa-wifi' : 'fa-cart-shopping'}"></i> ${message}`;
            document.body.appendChild(toast);
            setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 500); }, 2300);
        }

        // Service cart buttons
        document.querySelectorAll('.cart-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const serviceName = btn.getAttribute('data-service') || 'this service';
                showToast(`✨ ${serviceName} added to cart!`, false);
                btn.innerHTML = '<i class="fas fa-check"></i> Added!';
                setTimeout(() => { btn.innerHTML = '<i class="fas fa-cart-plus"></i> Add to Cart'; }, 1200);
            });
        });

        // Plan purchase buttons
        document.querySelectorAll('.btn-buy').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const planName = btn.getAttribute('data-plan') || 'selected plan';
                showToast(`🚀 ${planName} successfully purchased! Check email.`, true);
                btn.style.transform = 'scale(0.96)';
                setTimeout(() => { btn.style.transform = ''; }, 200);
            });
        });

        // smooth scroll for anchor links
        document.querySelectorAll('.nav-links a, .slide-btn').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const hash = this.getAttribute('href');
                if (hash && hash !== '#') {
                    e.preventDefault();
                    const target = document.querySelector(hash);
                    if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    </script>
</body>

</html>