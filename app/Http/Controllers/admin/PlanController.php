<?php

namespace App\Http\Controllers\admin;

use App\Models\WifiPlan; //Step 20 

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class PlanController extends Controller
{
    public function index()
    {
        $plans = WifiPlan::latest()->get();
        return view('admin.plans.index', compact('plans'));
    }

    public function create()
    {
        return view('admin.plans.create');
    }

    public function store(Request $request)
    {
        WifiPlan::create($request->all());

        return redirect('/admin/plans')->with('success', 'Plan Created');
    }

    // edit plan 
    public function edit($id)
    {
        $plan = WifiPlan::findOrFail($id);
        return view('admin.plans.edit', compact('plan'));
    }

    public function update(Request $request, $id)
    {
        $plan = WifiPlan::findOrFail($id);
        $plan->update($request->all());

        return redirect('/admin/plans')->with('success', 'Plan updated successfully');
    }

    // Delete
    public function destroy($id)
    {
        $plan = WifiPlan::findOrFail($id);
        $plan->delete();

        return redirect('/admin/plans')->with('success', 'Plan Deleted');
    }

    // user facing plans 
    public function userPlans()
    {
        $plans = WifiPlan::where('is_active', true)
            ->where('price', '>', 0) // Only show paid plans
            ->get();
        return view('plans', compact('plans'));
    }
}
