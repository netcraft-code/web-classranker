<?php

namespace CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\Ads;

use CustomFeature\ClassRanker\Models\AdWatch;
use CustomFeature\ClassRanker\Models\PremiumAccess;
use CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\ResourceController;
use Illuminate\Http\Request;

class AdController extends ResourceController
{
    public function watchAd(Request $request)
    {
        $customer = $this->resolveShopUser($request);

        $customer->increment('coins');

        // Ad watch record karo
        AdWatch::create([
            'customer_id' => $customer->id,
            'watched_at'  => now(),
        ]);

        // Last 24hr mein kitne ads dekhe?
        $adsWatched = AdWatch::where('customer_id', $customer->id)
            ->where('watched_at', '>=', now()->subHours(24))
            ->count();

        $premiumUnlocked = false;

        // 4 ads complete → 24hr premium access
        if ($adsWatched >= 8) {
            PremiumAccess::updateOrCreate(
                ['customer_id' => $customer->id],
                ['expires_at'  => now()->addHours(24)]
            );
            
            $premiumUnlocked = true;
        }

        return response()->json([
            'coins'           => $customer->fresh()->coins,
            'ads_watched'     => $adsWatched,
            'premium_unlocked'=> $premiumUnlocked,
            'ads_remaining'   => max(0, 8 - $adsWatched),
        ]);
    }

    // Premium status check
    public function premiumStatus(Request $request)
    {
        $customer = $this->resolveShopUser($request);

        // User khud premium hai?
        if ($customer->isPremium()) {
            return response()->json([
                'has_access'   => true,
                'type'         => 'subscription',
                'ads_watched'  => 0,
                'ads_remaining'=> 0,
                'coins'        => $customer->coins,
            ]);
        }

        // Ad se premium mila hai?
        $access = PremiumAccess::where('customer_id', $customer->id)
            ->where('expires_at', '>', now())
            ->first();

        $adsWatched = AdWatch::where('customer_id', $customer->id)
            ->where('watched_at', '>=', now()->subHours(24))
            ->count();

        return response()->json([
            'has_access'    => (bool) $access,
            'type'          => $access ? 'ad_reward' : null,
            'expires_at'    => $access?->expires_at,
            'ads_watched'   => $adsWatched,
            'ads_remaining' => max(0, 8 - $adsWatched),
            'coins'         => $customer->coins,
        ]);
    }
}
