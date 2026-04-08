@extends('admin.layout')
<meta name="viewport" content="width=device-width, initial-scale=1">
@section('content')

<div class="plan-form-container">
    <h2 class="form-title">✨ Create New Plan</h2>

    <form method="POST" action="/admin/plans" class="plan-form">
        @csrf

        <!-- Plan Name -->
        <div class="input-group">
            <label for="plan_name">Plan Name</label>
            <input type="text" id="plan_name" name="name" placeholder="e.g. Premium Surf" class="form-control">
        </div>

        <!-- Price -->
        <div class="input-group">
            <label for="price">Price ($)</label>
            <input type="number" id="price" name="price" placeholder="0.00" step="0.01" class="form-control" value="0.00">
        </div>

        <!-- Special Options -->
        <div class="input-group" style="display:flex; align-items:center; gap:10px; background:rgba(102,126,234,0.05); padding:10px; border-radius:15px;">
            <input type="checkbox" id="is_free" name="is_free" value="1" style="width:20px; height:20px;">
            <label for="is_free" style="margin-bottom:0;">Is this a <b>One-Time Free Plan</b>?</label>
        </div>

        <!-- Data Limit -->
        <div class="input-group">
            <label for="limit_bytes">Data Limit (MB)</label>
            <input type="number" id="limit_bytes" name="limit_bytes" placeholder="e.g. 1024" class="form-control" required>
            <small style="color: #666; font-size: 0.8rem;">Leave blank or 0 for Unlimited Data.</small>
        </div>

        <!-- Validity Type (Dropdown) -->
        <div class="input-group">
            <label for="validity">Validity Period</label>
            <select name="validity_type" id="validity" class="form-control">
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>  <!-- Fixed duplicate to Monthly -->
            </select>
        </div>

        <!-- Duration Minutes -->
        <div class="input-group">
            <label for="duration">Duration (minutes)</label>
            <input type="number" id="duration" name="duration_minutes" placeholder="e.g. 60" class="form-control">
        </div>

        <!-- MikroTik Profile -->
        <div class="input-group">
            <label for="profile_name">MikroTik Profile</label>
            <input type="text" id="profile_name" name="profile_name" placeholder="e.g. default" class="form-control" value="default">
        </div>

        <!-- Speed Limits (Mbps) -->
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:1.5rem;">
            <div class="input-group">
                <label for="upload_limit">Upload Speed (Mbps)</label>
                <input type="text" id="upload_limit" name="upload_limit" placeholder="e.g. 2M" class="form-control" value="2M">
            </div>
            <div class="input-group">
                <label for="download_limit">Download Speed (Mbps)</label>
                <input type="text" id="download_limit" name="download_limit" placeholder="e.g. 5M" class="form-control" value="5M">
            </div>
        </div>

        <!-- MikroTik Profile -->
        <div class="input-group">
            <label for="profile_name">MikroTik Profile</label>
            <input type="text" id="profile_name" name="profile_name" placeholder="e.g. default" class="form-control" value="default">
        </div>

        <!-- Submit Button -->
        <button type="submit" class="submit-btn">Create Plan</button>
    </form>
</div>

<style>
    /* Advanced Responsive Styling - No PHP changes */
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    .plan-form-container {
        max-width: 600px;
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
        width: 80px;
        height: 4px;
        background: linear-gradient(to right, #667eea, #764ba2);
        border-radius: 4px;
        margin: 0.5rem auto 0;
    }

    .plan-form {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        padding: 2rem 1.75rem;
        border-radius: 32px;
        box-shadow: 
            0 20px 40px -12px rgba(0, 0, 0, 0.25),
            0 8px 24px -6px rgba(102, 126, 234, 0.15),
            inset 0 1px 2px rgba(255, 255, 255, 0.8);
        border: 1px solid rgba(255, 255, 255, 0.3);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .plan-form:hover {
        box-shadow: 
            0 28px 48px -16px rgba(0, 0, 0, 0.3),
            0 10px 30px -8px rgba(102, 126, 234, 0.25);
        transform: translateY(-4px);
    }

    .input-group {
        margin-bottom: 1.5rem;
    }

    .input-group label {
        display: block;
        font-weight: 600;
        font-size: 0.95rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #4a5568;
        margin-bottom: 0.5rem;
        transition: color 0.2s;
    }

    .form-control {
        width: 100%;
        /* padding: 0.9rem 1.1rem; */
        padding: 0.8rem 1.0rem;;
        font-size: 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 20px;
        background-color: white;
        transition: all 0.25s ease;
        outline: none;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
    }

    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.15);
        background-color: #fff;
    }

    .form-control:hover:not(:focus) {
        border-color: #cbd5e0;
        background-color: #fafafa;
    }

    /* Remove number input spinners for cleaner look */
    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    input[type=number] {
        -moz-appearance: textfield;
    }

    /* Select dropdown custom styling */
    select.form-control {
        appearance: none;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%234a5568' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        background-size: 1.2em;
        padding-right: 2.5rem;
    }

    /* Submit button */
    .submit-btn {
        width: 100%;
        padding: 0.8rem 1rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-weight: 700;
        font-size: 1.2rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        border: none;
        border-radius: 40px;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 8px 20px -6px rgba(102, 126, 234, 0.5);
        margin-top: 1rem;
        position: relative;
        overflow: hidden;
    }

    .submit-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        transition: left 0.5s ease;
    }

    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px -8px rgba(102, 126, 234, 0.7);
        background: linear-gradient(135deg, #5a6fd8 0%, #6a4292 100%);
    }

    .submit-btn:hover::before {
        left: 100%;
    }

    .submit-btn:active {
        transform: translateY(0);
        box-shadow: 0 4px 16px -4px rgba(102, 126, 234, 0.6);
    }

    /* Responsive adjustments */
    @media (max-width: 640px) {
        .plan-form-container {
            margin: 1rem auto;
            padding: 0 1rem;
        }

        .form-title {
            font-size: 1.8rem;
        }

        .plan-form {
            padding: 1.8rem 1.2rem;
            border-radius: 28px;
        }

        .input-group label {
            font-size: 0.85rem;
        }

        .form-control {
            padding: 0.9rem 1rem;
            font-size: 0.95rem;
            border-radius: 18px;
        }

        .submit-btn {
            padding: 0.9rem 1.2rem;
            font-size: 1.1rem;
        }
    }

    /* Extra small devices */
    @media (max-width: 380px) {
        .form-title {
            font-size: 1.5rem;
        }

        .plan-form {
            padding: 1.5rem 1rem;
        }
    }

    /* Preserve original table styling (if any) – kept for compatibility */
    table {
        width: 100%;
        overflow-x: auto;
    }
</style>
@endsection