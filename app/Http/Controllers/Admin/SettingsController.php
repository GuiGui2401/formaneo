<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Settings;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'quiz_reward_per_correct' => Settings::getValue('quiz_reward_per_correct', 20),
            'quiz_passing_score' => Settings::getValue('quiz_passing_score', 60),
            'level1_commission' => Settings::getValue('level1_commission', 1000),
            'level2_commission' => Settings::getValue('level2_commission', 500),
            'welcome_bonus' => Settings::getValue('welcome_bonus', 1000),
            'min_withdrawal_amount' => Settings::getValue('min_withdrawal_amount', 1000),
            'max_withdrawal_amount' => Settings::getValue('max_withdrawal_amount', 1000000),
            'cashback_rate' => Settings::getValue('cashback_rate', 0.15),
            'free_quizzes_per_user' => Settings::getValue('free_quizzes_per_user', 5),
            'support_email' => Settings::getValue('support_email', 'support@formaneo.com'),
            'support_phone' => Settings::getValue('support_phone', '+225 XX XX XX XX XX'),
            'support_whatsapp' => Settings::getValue('support_whatsapp', '+225XXXXXXXXXX'),
            'account_activation_cost' => Settings::getValue('account_activation_cost', 5000),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'quiz_reward_per_correct' => 'required|numeric|min:0',
            'quiz_passing_score' => 'required|integer|min:0|max:100',
            'level1_commission' => 'required|numeric|min:0',
            'level2_commission' => 'required|numeric|min:0',
            'welcome_bonus' => 'required|numeric|min:0',
            'min_withdrawal_amount' => 'required|numeric|min:0',
            'max_withdrawal_amount' => 'required|numeric|min:0',
            'cashback_rate' => 'required|numeric|min:0|max:1',
            'free_quizzes_per_user' => 'required|integer|min:0',
            'support_email' => 'nullable|email',
            'support_phone' => 'nullable|string|max:20',
            'support_whatsapp' => 'nullable|string|max:20',
            'account_activation_cost' => 'required|numeric|min:100',
        ]);

        foreach ($request->only([
            'quiz_reward_per_correct',
            'quiz_passing_score',
            'level1_commission',
            'level2_commission',
            'welcome_bonus',
            'min_withdrawal_amount',
            'max_withdrawal_amount',
            'cashback_rate',
            'free_quizzes_per_user',
            'support_email',
            'support_phone',
            'support_whatsapp',
            'account_activation_cost'
        ]) as $key => $value) {
            Settings::setValue($key, $value);
        }

        return back()->with('success', 'Paramètres mis à jour avec succès.');
    }
}
