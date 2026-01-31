<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\FeatureList;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Models\UserSubscription;
class PackageController extends Controller
{
    public function index()
    {
        return view('admin.package.index');
    }

    // AJAX Data for Index Table
    public function data(Request $request)
    {
        try {
            $query = Package::query();
            if ($request->filled('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }
            $data = $query->orderBy('id', 'desc')->paginate(10);

            return response()->json([
                'data' => $data->items(),
                'total' => $data->total(),
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
            ]);
        } catch (Exception $e) {
            Log::error("Error fetching package data: " . $e->getMessage());
            return response()->json(['error' => 'Something went wrong!'], 500);
        }
    }

    public function create()
    {
        try {
            $features = FeatureList::where('status', 1)->get();
            return view('admin.package.create', compact('features'));
        } catch (Exception $e) {
            Log::error("Error loading package create page: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load create page.');
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required',
            'original_price' => 'required|numeric',
            'price' => 'required|numeric',
        ]);

        try {
            // Unique Slug Generation
            $slug = Str::slug($request->name);
            if (Package::where('slug', $slug)->exists()) {
                $slug = $slug . '-' . Str::lower(Str::random(5));
            }

            $package = Package::create($request->all() + [
                'slug' => $slug,
                'is_popular' => $request->has('is_popular') ? 1 : 0
            ]);

            // Sync Features with Values
            if ($request->has('features')) {
                $syncData = [];
                foreach ($request->features as $featureId => $data) {
                    if (isset($data['selected'])) {
                        $syncData[$featureId] = ['value' => $data['value'] ?? null];
                    }
                }
                $package->features()->sync($syncData);
            }

            Log::info("Package created successfully: ID " . $package->id);
            return redirect()->route('package.index')->with('success', 'Package created successfully!');

        } catch (Exception $e) {
            Log::error("Error storing package: " . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Failed to save package. Please try again.');
        }
    }

   public function show($id)
{
    // features এবং pivot value সহ প্যাকেজ লোড
    $package = Package::with('features')->findOrFail($id);
    
    $now = \Carbon\Carbon::now('Asia/Dhaka');
    $oneWeekFromNow = $now->copy()->addDays(7);

    // ১. একটিভ ইউজার লিস্ট
    $activeUsers = UserSubscription::where('package_id', $id)
                    ->where('status', 'active')
                    ->where('end_date', '>', $now)
                    ->with('user')
                    ->get();

    // ২. ১ সপ্তাহের মধ্যে এক্সপায়ার হবে এমন ইউজার
    $expiringSoon = UserSubscription::where('package_id', $id)
                    ->where('status', 'active')
                    ->whereBetween('end_date', [$now, $oneWeekFromNow])
                    ->with('user')
                    ->get();

    return view('admin.package.show', compact('package', 'activeUsers', 'expiringSoon'));
}

    public function edit($id)
    {
        try {
            $package = Package::with('features')->findOrFail($id);
            $features = FeatureList::where('status', 1)->get();
            $selectedFeatures = $package->features->pluck('pivot.value', 'id')->toArray();

            return view('admin.package.edit', compact('package', 'features', 'selectedFeatures'));
        } catch (Exception $e) {
            Log::error("Error loading package edit page for ID $id: " . $e->getMessage());
            return redirect()->route('package.index')->with('error', 'Failed to load edit page.');
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
        ]);

        try {
            $package = Package::findOrFail($id);

            // Maintain Unique Slug on Name Change
            $slug = $package->slug;
            if ($package->name !== $request->name) {
                $slug = Str::slug($request->name);
                if (Package::where('slug', $slug)->where('id', '!=', $id)->exists()) {
                    $slug = $slug . '-' . Str::lower(Str::random(5));
                }
            }

            $package->update($request->all() + [
                'slug' => $slug,
                'is_popular' => $request->has('is_popular') ? 1 : 0
            ]);

            if ($request->has('features')) {
                $syncData = [];
                foreach ($request->features as $featureId => $data) {
                    if (isset($data['selected'])) {
                        $syncData[$featureId] = ['value' => $data['value'] ?? null];
                    }
                }
                $package->features()->sync($syncData);
            }

            Log::info("Package updated successfully: ID " . $id);
            return redirect()->route('package.index')->with('success', 'Package updated successfully!');

        } catch (Exception $e) {
            Log::error("Error updating package ID $id: " . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Failed to update package.');
        }
    }

    public function destroy($id)
    {
        try {
            $package = Package::findOrFail($id);
            $package->delete();

            Log::info("Package deleted: ID " . $id);
            return redirect()->route('package.index')->with('success', 'Package deleted successfully!');
        } catch (Exception $e) {
            Log::error("Error deleting package ID $id: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete package.');
        }
    }
}