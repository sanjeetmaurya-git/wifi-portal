@extends('admin.layout')
<meta name="viewport" content="width=device-width, initial-scale=1">
@section('content')

<div class="plan-form-container">
    <h2 class="form-title">✨ Create New Plan</h2>

    <form method="POST" action="/admin/plans" class="plan-form" id="planForm">
        @csrf

        <!-- Plan Name -->
        <div class="input-group">
            <label for="plan_name">Plan Name</label>
            <input type="text" id="plan_name" name="name" placeholder="e.g. Premium Daily 1GB" class="form-control" required>
        </div>

        <!-- Plan Type -->
        <div class="input-group">
            <label for="plan_type">Plan Type</label>
            <select name="plan_type" id="plan_type" class="form-control" onchange="handlePlanTypeChange(this.value)" required>
                <option value="">— Select Type —</option>
                <option value="daily">📅 Daily Plan (data resets every midnight)</option>
                <option value="unlimited">♾️ Unlimited Plan (speed-limited, no data cap)</option>
                <option value="datapack">🚀 Data Pack (one-time top-up — stacks on daily)</option>
            </select>
            <small class="hint" id="type-hint" style="color:#888; margin-top:4px; display:block;"></small>
        </div>

        <!-- Daily Data (MB/day) — shows only for daily -->
        <div class="input-group type-field daily-field" id="daily_data_wrap" style="display:none;">
            <label for="daily_data_mb">Daily Data Allowance (MB/day)</label>
            <input type="number" id="daily_data_mb" name="daily_data_mb" placeholder="e.g. 1024 for 1 GB/day" class="form-control">
            <small style="color:#888;">Data this user gets every day. Resets at midnight.</small>
        </div>

        <!-- Total Data / Pack Size — shows for daily (optional cap) and datapack -->
        <div class="input-group type-field data-limit-field" id="limit_bytes_wrap" style="display:none;">
            <label for="limit_bytes" id="limit_bytes_label">Data Limit (MB)</label>
            <input type="number" id="limit_bytes" name="limit_bytes" placeholder="e.g. 5120" class="form-control">
            <small style="color:#888;" id="limit_bytes_hint">Leave blank for unlimited.</small>
        </div>

        <!-- Description -->
        <div class="input-group">
            <label for="description">Description <small style="font-weight:400; text-transform:none;">(optional)</small></label>
            <input type="text" id="description" name="description" placeholder="e.g. Best value for daily users" class="form-control">
        </div>

        <!-- Price -->
        <div class="input-group">
            <label for="price">Price (₹)</label>
            <input type="number" id="price" name="price" placeholder="0.00" step="0.01" class="form-control" value="0.00">
        </div>

        <!-- Special Options -->
        <div class="input-group" style="display:flex; align-items:center; gap:10px; background:rgba(102,126,234,0.05); padding:12px 16px; border-radius:15px;">
            <input type="checkbox" id="is_free" name="is_free" value="1" style="width:20px; height:20px;">
            <label for="is_free" style="margin-bottom:0;">Is this a <b>One-Time Free Trial Plan</b>?</label>
        </div>

        <!-- Validity Period -->
        <div class="input-group" style="margin-top:1.5rem;">
            <label for="validity">Validity Period</label>
            <select name="validity_type" id="validity" class="form-control">
                <option value="daily">Daily (1 day)</option>
                <option value="weekly">Weekly (7 days)</option>
                <option value="monthly">Monthly (30 days)</option>
            </select>
        </div>

        <!-- Duration in Minutes -->
        <div class="input-group">
            <label for="duration">Duration (minutes)</label>
            <input type="number" id="duration" name="duration_minutes" placeholder="e.g. 1440 = 1 day, 10080 = 7 days" class="form-control">
            <small style="color:#888;">1440 = 1 day | 10080 = 1 week | 43200 = 30 days</small>
        </div>

        <!-- Speed Limits -->
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:1.5rem;">
            <div class="input-group">
                <label for="upload_limit">Upload Speed</label>
                <input type="text" id="upload_limit" name="upload_limit" placeholder="e.g. 2M" class="form-control" value="2M">
            </div>
            <div class="input-group">
                <label for="download_limit">Download Speed</label>
                <input type="text" id="download_limit" name="download_limit" placeholder="e.g. 5M" class="form-control" value="5M">
                <small style="color:#888;">For unlimited plans, this is the speed cap.</small>
            </div>
        </div>

        <!-- MikroTik Profile -->
        <div class="input-group">
            <label for="profile_name">MikroTik Profile Name</label>
            <input type="text" id="profile_name" name="profile_name" placeholder="e.g. daily-1gb" class="form-control" value="default">
            <small style="color:#888;">Must match an existing profile in Winbox → IP → Hotspot → User Profiles.</small>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="submit-btn">Create Plan</button>
    </form>
</div>

<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    .plan-form-container {
        max-width: 620px;
        margin: 2rem auto;
        padding: 0 1.5rem;
        font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    }

    .form-title {
        font-size: 2rem;
        font-weight: 600;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 1.5rem;
        text-align: center;
        letter-spacing: -0.5px;
        position: relative;
    }
    .form-title::after {
        content: '';
        display: block;
        width: 80px; height: 4px;
        background: linear-gradient(to right, #667eea, #764ba2);
        border-radius: 4px;
        margin: 0.5rem auto 0;
    }

    .plan-form {
        background: rgba(255,255,255,0.96);
        backdrop-filter: blur(10px);
        padding: 2rem 1.75rem;
        border-radius: 32px;
        box-shadow: 0 20px 40px -12px rgba(0,0,0,0.25), 0 8px 24px -6px rgba(102,126,234,0.15);
        border: 1px solid rgba(255,255,255,0.3);
    }

    .input-group { margin-bottom: 1.5rem; }
    .input-group label {
        display: block;
        font-weight: 600;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #4a5568;
        margin-bottom: 0.5rem;
    }
    .form-control {
        width: 100%;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 16px;
        background: white;
        transition: all 0.25s;
        outline: none;
    }
    .form-control:focus { border-color: #667eea; box-shadow: 0 0 0 4px rgba(102,126,234,0.15); }
    .form-control:hover:not(:focus) { border-color: #cbd5e0; background: #fafafa; }

    /* Hide number spinners */
    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
    input[type=number] { -moz-appearance: textfield; }

    /* Dropdown arrow */
    select.form-control {
        appearance: none;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%234a5568' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        background-size: 1.2em;
        padding-right: 2.5rem;
    }

    /* Plan type highlight strip */
    .type-highlight {
        padding: 10px 16px;
        border-radius: 12px;
        font-size: 0.85rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
        display: none;
    }
    .type-highlight.daily    { background: rgba(34,197,94,0.12);  border: 1px solid rgba(34,197,94,0.3);  color: #15803d; }
    .type-highlight.unlimited { background: rgba(6,182,212,0.12);  border: 1px solid rgba(6,182,212,0.3);  color: #0369a1; }
    .type-highlight.datapack  { background: rgba(245,158,11,0.12); border: 1px solid rgba(245,158,11,0.3); color: #b45309; }

    .submit-btn {
        width: 100%;
        padding: 0.9rem 1rem;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        font-weight: 700;
        font-size: 1.1rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        border: none;
        border-radius: 40px;
        cursor: pointer;
        transition: all 0.3s;
        box-shadow: 0 8px 20px -6px rgba(102,126,234,0.5);
        margin-top: 0.5rem;
    }
    .submit-btn:hover { transform: translateY(-2px); box-shadow: 0 12px 28px -8px rgba(102,126,234,0.7); }

    @media (max-width: 640px) {
        .plan-form-container { padding: 0 1rem; }
        .plan-form { padding: 1.5rem 1rem; border-radius: 24px; }
    }
</style>

<script>
const hints = {
    daily: '📅 Users get X MB every day. Data resets at midnight. Two daily plans cannot run simultaneously — the 2nd one queues.',
    unlimited: '♾️ No data cap but speed is throttled to your set limit. Great for overnight/longer usage.',
    datapack: '🚀 One-time MB boost that stacks on top of an active Daily Plan. User must have a daily plan active to purchase.',
};

function handlePlanTypeChange(type) {
    // Hide all conditional fields
    document.querySelectorAll('.daily-field').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.data-limit-field').forEach(el => el.style.display = 'none');

    // Update hint
    document.getElementById('type-hint').textContent = hints[type] || '';

    if (type === 'daily') {
        document.getElementById('daily_data_wrap').style.display = 'block';
        document.getElementById('limit_bytes_wrap').style.display = 'block';
        document.getElementById('limit_bytes_label').textContent = 'Total Data Cap (MB) — optional';
        document.getElementById('limit_bytes_hint').textContent = 'Optional: max total MB across the whole validity period. Leave blank for no cap.';
        // Auto-suggest profile name
        document.getElementById('profile_name').placeholder = 'e.g. daily-plan';
    } else if (type === 'unlimited') {
        document.getElementById('profile_name').placeholder = 'e.g. unlimited-plan';
    } else if (type === 'datapack') {
        document.getElementById('limit_bytes_wrap').style.display = 'block';
        document.getElementById('limit_bytes_label').textContent = 'Data Pack Size (MB) *';
        document.getElementById('limit_bytes_hint').textContent = 'Total MB this pack gives the user. Required.';
        document.getElementById('profile_name').placeholder = 'e.g. datapack';
    }
}
</script>
@endsection