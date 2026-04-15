<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Your WiFi Plan – SpeedWave</title>
    <meta name="description" content="Select a daily, unlimited, or data pack plan to get blazing-fast internet access.">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg: #0d0d1f;
            --card: rgba(255,255,255,0.05);
            --border: rgba(255,255,255,0.1);
            --text: #f1f5f9;
            --muted: #94a3b8;
            --green: #22c55e;
            --indigo: #6366f1;
            --amber: #f59e0b;
            --cyan: #06b6d4;
            --red: #ef4444;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: var(--bg);
            font-family: 'Outfit', sans-serif;
            color: var(--text);
            min-height: 100vh;
        }
        body::before {
            content: '';
            position: fixed; inset: 0; z-index: 0;
            background:
                radial-gradient(ellipse 70% 50% at 10% 10%, rgba(99,102,241,0.12), transparent),
                radial-gradient(ellipse 60% 60% at 90% 80%, rgba(6,182,212,0.08), transparent);
            pointer-events: none;
        }

        .header {
            position: relative; z-index: 1;
            text-align: center;
            padding: 3rem 1rem 1.5rem;
        }
        .header h1 {
            font-size: 2.2rem;
            font-weight: 800;
            background: linear-gradient(to right, #818cf8, #34d399);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .header p {
            color: var(--muted);
            margin-top: 0.5rem;
            font-size: 1rem;
        }

        /* Tab bar */
        .tab-bar {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            padding: 1rem;
            flex-wrap: wrap;
            position: relative; z-index: 1;
        }
        .tab-btn {
            padding: 0.55rem 1.4rem;
            border-radius: 100px;
            border: 1px solid var(--border);
            background: transparent;
            color: var(--muted);
            font-family: 'Outfit', sans-serif;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.25s;
        }
        .tab-btn.active, .tab-btn:hover { border-color: var(--indigo); background: rgba(99,102,241,0.15); color: #fff; }
        .tab-btn[data-type="daily"].active  { border-color: var(--green);  background: rgba(34,197,94,0.12); }
        .tab-btn[data-type="unlimited"].active { border-color: var(--cyan); background: rgba(6,182,212,0.12); }
        .tab-btn[data-type="datapack"].active  { border-color: var(--amber); background: rgba(245,158,11,0.12); }

        /* Section headings */
        .section-label {
            text-align: center;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 2rem 0 1.2rem;
            position: relative; z-index: 1;
        }
        .section-label.daily    { color: var(--green); }
        .section-label.unlimited { color: var(--cyan); }
        .section-label.datapack  { color: var(--amber); }

        /* Plans grid */
        .plans-section {
            padding: 0 1rem 1rem;
            max-width: 1200px;
            margin: 0 auto;
            position: relative; z-index: 1;
        }
        .plans-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 1.5rem;
        }

        /* Plan card */
        .plan-card {
            background: var(--card);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 2rem 1.75rem;
            width: 280px;
            position: relative;
            overflow: visible;
            transition: transform 0.3s, box-shadow 0.3s, border-color 0.3s;
        }
        .plan-card:hover {
            transform: translateY(-6px);
        }
        .plan-card.daily    { border-color: rgba(34,197,94,0.2); }
        .plan-card.daily:hover { border-color: rgba(34,197,94,0.5); box-shadow: 0 20px 40px rgba(34,197,94,0.12); }
        .plan-card.unlimited { border-color: rgba(6,182,212,0.2); }
        .plan-card.unlimited:hover { border-color: rgba(6,182,212,0.5); box-shadow: 0 20px 40px rgba(6,182,212,0.12); }
        .plan-card.datapack  { border-color: rgba(245,158,11,0.2); }
        .plan-card.datapack:hover  { border-color: rgba(245,158,11,0.5); box-shadow: 0 20px 40px rgba(245,158,11,0.12); }

        /* Badge */
        .badge {
            position: absolute;
            top: -12px; right: 16px;
            padding: 4px 14px;
            border-radius: 100px;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-free   { background: var(--red);    color: #fff; }
        .badge-daily  { background: var(--green);  color: #fff; }
        .badge-unlim  { background: var(--cyan);   color: #0a0a1a; }
        .badge-data   { background: var(--amber);  color: #0a0a1a; }

        .plan-name {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 0.3rem;
        }
        .plan-type-label {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 1.2rem;
        }
        .plan-type-label.daily    { color: var(--green); }
        .plan-type-label.unlimited { color: var(--cyan); }
        .plan-type-label.datapack  { color: var(--amber); }

        .plan-price {
            font-size: 2.8rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 1.5rem;
        }
        .plan-price span { font-size: 1rem; font-weight: 400; color: var(--muted); }

        .plan-features {
            list-style: none;
            margin-bottom: 1.75rem;
            border-top: 1px solid var(--border);
            padding-top: 1rem;
        }
        .plan-features li {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 0.9rem;
            color: rgba(255,255,255,0.75);
            margin-bottom: 0.6rem;
        }
        .plan-features li i { width: 16px; text-align: center; font-size: 0.8rem; }
        .feat-daily    i { color: var(--green); }
        .feat-unlimited i { color: var(--cyan); }
        .feat-datapack  i { color: var(--amber); }

        /* Buttons */
        .btn-buy {
            width: 100%;
            padding: 0.95rem;
            border-radius: 14px;
            border: none;
            font-family: 'Outfit', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.25s;
        }
        .btn-buy.daily    { background: linear-gradient(135deg, #22c55e, #16a34a); color: #fff; box-shadow: 0 8px 20px rgba(34,197,94,0.25); }
        .btn-buy.unlimited { background: linear-gradient(135deg, #06b6d4, #0891b2); color: #fff; box-shadow: 0 8px 20px rgba(6,182,212,0.25); }
        .btn-buy.datapack  { background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; box-shadow: 0 8px 20px rgba(245,158,11,0.25); }
        .btn-buy.free-plan { background: linear-gradient(135deg, #6366f1, #4f46e5); color: #fff; }
        .btn-buy:hover { transform: translateY(-2px); filter: brightness(1.1); }
        .btn-buy:disabled { background: #334155 !important; cursor: not-allowed; transform: none !important; box-shadow: none !important; color: var(--muted) !important; }

        /* Datapack locked notice */
        .datapack-locked {
            margin: 0 auto 2rem;
            max-width: 500px;
            background: rgba(245,158,11,0.07);
            border: 1px solid rgba(245,158,11,0.25);
            border-radius: 14px;
            padding: 0.85rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.88rem;
            color: #fcd34d;
            text-align: left;
            position: relative; z-index: 1;
        }

        footer {
            text-align: center;
            padding: 2rem;
            color: var(--muted);
            font-size: 0.8rem;
            position: relative; z-index: 1;
        }

        @media (max-width: 480px) {
            .plan-card { width: 100%; max-width: 340px; }
            .header h1 { font-size: 1.7rem; }
        }
    </style>
</head>

<body>
    @include('partials.header')

    <div class="header">
        <h1>Choose Your Plan</h1>
        <p>Select the perfect plan to stay connected — fast, reliable internet.</p>
    </div>

    {{-- ── Tab navigation ── --}}
    <div class="tab-bar">
        <button class="tab-btn active" data-type="all" onclick="filterPlans('all')">
            <i class="fas fa-th"></i> All Plans
        </button>
        @if($plans->where('plan_type','daily')->isNotEmpty())
        <button class="tab-btn" data-type="daily" onclick="filterPlans('daily')">
            <i class="fas fa-calendar-day"></i> Daily Plans
        </button>
        @endif
        @if($plans->where('plan_type','unlimited')->isNotEmpty())
        <button class="tab-btn" data-type="unlimited" onclick="filterPlans('unlimited')">
            <i class="fas fa-infinity"></i> Unlimited
        </button>
        @endif
        @if($plans->where('plan_type','datapack')->isNotEmpty())
        <button class="tab-btn" data-type="datapack" onclick="filterPlans('datapack')">
            <i class="fas fa-rocket"></i> Data Packs
        </button>
        @endif
    </div>

    <div class="plans-section">

        {{-- ────────────── DAILY PLANS ────────────── --}}
        @php $dailyPlans = $plans->where('plan_type','daily'); @endphp
        @if($dailyPlans->isNotEmpty())
        <div class="section-label daily plan-section" data-type="daily">
            <i class="fas fa-calendar-day"></i> Daily Data Plans
        </div>
        <div class="plans-grid plan-section" data-type="daily">
            @foreach($dailyPlans as $plan)
            <div class="plan-card daily">
                @if($plan->is_free)
                    <div class="badge badge-free">FREE TRIAL</div>
                @else
                    <div class="badge badge-daily">Daily</div>
                @endif
                <div class="plan-name">{{ $plan->name }}</div>
                <div class="plan-type-label daily"><i class="fas fa-calendar-day"></i> Daily Reset</div>
                <div class="plan-price" style="color:#4ade80;">
                    @if($plan->is_free) FREE <span>/ one-time</span>
                    @else ₹{{ $plan->price }} <span>/ plan</span>
                    @endif
                </div>
                <ul class="plan-features feat-daily">
                    <li><i class="fas fa-database"></i> {{ $plan->daily_data_mb ? $plan->daily_data_mb.' MB/Day' : 'Unlimited Data' }}</li>
                    <li><i class="fas fa-clock"></i> {{ $plan->validity_label }}</li>
                    <li><i class="fas fa-tachometer-alt"></i> {{ $plan->download_limit ?: 'Best' }} Down / {{ $plan->upload_limit ?: 'Best' }} Up</li>
                    @if($plan->description)
                    <li><i class="fas fa-info-circle"></i> {{ $plan->description }}</li>
                    @endif
                </ul>
                <form action="/create-order" method="POST">
                    @csrf
                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                    @php $isClaimed = $plan->is_free && in_array($plan->id, $claimedFreePlans ?? []); @endphp
                    <button type="submit" class="btn-buy {{ $plan->is_free ? 'free-plan' : 'daily' }}" {{ $isClaimed ? 'disabled' : '' }}>
                        {{ $isClaimed ? '✓ Already Claimed' : ($plan->is_free ? 'Claim Free Trial' : 'Buy Now') }}
                    </button>
                </form>
            </div>
            @endforeach
        </div>
        @endif

        {{-- ────────────── UNLIMITED PLANS ────────────── --}}
        @php $unlimitedPlans = $plans->where('plan_type','unlimited'); @endphp
        @if($unlimitedPlans->isNotEmpty())
        <div class="section-label unlimited plan-section" data-type="unlimited">
            <i class="fas fa-infinity"></i> Unlimited Plans
        </div>
        <div class="plans-grid plan-section" data-type="unlimited">
            @foreach($unlimitedPlans as $plan)
            <div class="plan-card unlimited">
                <div class="badge badge-unlim">Unlimited</div>
                <div class="plan-name">{{ $plan->name }}</div>
                <div class="plan-type-label unlimited"><i class="fas fa-infinity"></i> No Data Cap</div>
                <div class="plan-price" style="color:#22d3ee;">₹{{ $plan->price }} <span>/ plan</span></div>
                <ul class="plan-features feat-unlimited">
                    <li><i class="fas fa-infinity"></i> Unlimited Data</li>
                    <li><i class="fas fa-clock"></i> {{ $plan->validity_label }}</li>
                    <li><i class="fas fa-tachometer-alt"></i> {{ $plan->download_limit ?: 'Best Available' }} Speed</li>
                    @if($plan->description)
                    <li><i class="fas fa-info-circle"></i> {{ $plan->description }}</li>
                    @endif
                </ul>
                <form action="/create-order" method="POST">
                    @csrf
                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                    <button type="submit" class="btn-buy unlimited">Buy Now</button>
                </form>
            </div>
            @endforeach
        </div>
        @endif

        {{-- ────────────── DATA PACKS ────────────── --}}
        @php $dataPacks = $plans->where('plan_type','datapack'); @endphp
        @if($dataPacks->isNotEmpty())
        <div class="section-label datapack plan-section" data-type="datapack">
            <i class="fas fa-rocket"></i> Data Pack Top-Ups
        </div>
        <div class="plans-grid plan-section" data-type="datapack">
            @foreach($dataPacks as $plan)
            <div class="plan-card datapack">
                <div class="badge badge-data">Top-Up</div>
                <div class="plan-name">{{ $plan->name }}</div>
                <div class="plan-type-label datapack"><i class="fas fa-bolt"></i> Instant Activation</div>
                <div class="plan-price" style="color:#fbbf24;">₹{{ $plan->price }} <span>/ pack</span></div>
                <ul class="plan-features feat-datapack">
                    <li><i class="fas fa-database"></i> {{ $plan->limit_bytes ?? $plan->data_limit_mb }} MB Bonus Data</li>
                    <li><i class="fas fa-layer-group"></i> Stacks on active Daily Plan</li>
                    <li><i class="fas fa-bolt"></i> Activates instantly after payment</li>
                    @if($plan->description)
                    <li><i class="fas fa-info-circle"></i> {{ $plan->description }}</li>
                    @endif
                </ul>
                <form action="/create-order" method="POST">
                    @csrf
                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                    <button type="submit" class="btn-buy datapack">
                        <i class="fas fa-rocket"></i> Buy Data Pack
                    </button>
                </form>
            </div>
            @endforeach
        </div>
        @endif

    </div>

    <footer>&copy; 2026 SpeedWave Captive Portal — All internet usage is logged for 14 months as per Govt. guidelines.</footer>

    <script>
        // Initialize filter from URL query param (e.g. /plans?filter=datapack)
        const urlFilter = new URLSearchParams(location.search).get('filter') || 'all';
        filterPlans(urlFilter);

        function filterPlans(type) {
            // Update tab buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.toggle('active', btn.dataset.type === type || (type === 'all' && btn.dataset.type === 'all'));
            });

            // Show/hide plan sections
            document.querySelectorAll('.plan-section').forEach(el => {
                const show = type === 'all' || el.dataset.type === type;
                el.style.display = show ? (el.classList.contains('plans-grid') ? 'flex' : 'block') : 'none';
            });
        }
    </script>
</body>
</html>