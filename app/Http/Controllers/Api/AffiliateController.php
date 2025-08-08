<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AffiliateService;
use App\Models\AffiliateLink;
use Illuminate\Support\Str;

class AffiliateController extends Controller
{
    protected $affiliate;

    public function __construct(AffiliateService $affiliate)
    {
        $this->affiliate = $affiliate;
    }

    // générer un lien d'affiliation (ou récupérer existant)
    public function generate(Request $request)
    {
        $user = $request->user();

        $link = AffiliateLink::firstOrCreate(
            ['user_id' => $user->id],
            ['code' => $user->promo_code ?? strtoupper(Str::random(6)),
             'url' => $user->affiliate_link ?? config('app.url').'/invite/'.($user->promo_code ?? strtoupper(Str::random(6)))]
        );

        return response()->json($link);
    }

    // stats d'affiliation
    public function stats(Request $request)
    {
        $user = $request->user();
        $stats = $this->affiliate->getUserStats($user);

        return response()->json($stats);
    }
}
