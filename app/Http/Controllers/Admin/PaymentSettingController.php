<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentSettingController extends Controller
{
    // ১. সেটিংস পেজ প্রদর্শন
    public function index() {
        $settings = DB::table('payment_settings')->get();
        return view('admin.settings.payment_method', compact('settings'));
    }

    // ২. নতুন মেথড অ্যাড করা
    public function store(Request $request) {
        $request->validate([
            'provider' => 'required|string|max:50',
            'number'   => 'required|string|max:20',
        ]);

        DB::table('payment_settings')->insert([
            'provider'    => $request->provider,
            'number'      => $request->number,
            'type'        => $request->type ?? 'Personal',
            'instruction' => $request->instruction,
            'status'      => 1, // ডিফল্টভাবে একটিভ থাকবে
            'created_at'  => now(),
            'updated_at'  => now()
        ]);

        return redirect()->back()->with('success', 'নতুন পেমেন্ট মেথড যুক্ত হয়েছে।');
    }

    // ৩. মেথডগুলো আপডেট করা (ইনঅ্যাক্টিভ করার লজিক এখানে)
    public function update(Request $request) {
        if ($request->has('settings')) {
            foreach ($request->settings as $id => $data) {
                DB::table('payment_settings')->where('id', $id)->update([
                    'number'      => $data['number'],
                    'type'        => $data['type'],
                    'instruction' => $data['instruction'],
                    'status'      => isset($data['status']) ? 1 : 0, // চেকবক্স আনচেক থাকলে ০ বা ইনঅ্যাক্টিভ
                    'updated_at'  => now()
                ]);
            }
        }
        return redirect()->back()->with('success', 'পেমেন্ট সেটিংস আপডেট হয়েছে।');
    }

    // ৪. মেথড ডিলিট করা
    public function destroy($id) {
        DB::table('payment_settings')->where('id', $id)->delete();
        return redirect()->back()->with('success', 'পেমেন্ট মেথডটি ডিলিট করা হয়েছে।');
    }
}