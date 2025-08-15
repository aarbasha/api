<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Visitor;
use Torann\GeoIP\Facades\GeoIP;
use Detection\MobileDetect;

class TrackVisitors
{
    public function handle($request, Closure $next)
    {
        $ipAddress = $request->ip();
        // $location = GeoIP::getLocation($ipAddress);
        $detect = new MobileDetect;

        $deviceType = $detect->isMobile() ? 'Mobile' : ($detect->isTablet() ? 'Tablet' : 'Desktop');
        $userAgent = $request->header('User-Agent');
        $os = $this->getOS($userAgent);

        Visitor::create([
            'ip_address' => $ipAddress,
            'country' => $location['country'] ?? null,
            'region' => $location['region'] ?? null,
            'city' => $location['city'] ?? null,
            'latitude' => $location['lat'] ?? null,
            'longitude' => $location['lon'] ?? null,
            'visit_time' => now(),
            'device_type' => $deviceType,
            'os' => $os,
        ]);

        return $next($request);
    }

    private function getOS($userAgent)
    {
        // هنا يمكنك وضع منطق لاستخراج نظام التشغيل كما هو موضح سابقًا
    }
}
