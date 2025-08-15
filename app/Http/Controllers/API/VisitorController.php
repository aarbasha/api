<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Visitor;
use Torann\GeoIP\Facades\GeoIP;
use Detection\MobileDetect;

class VisitorController extends Controller
{
    public function store()
    {
        $ipAddress = request()->ip();
        $location = GeoIP::getLocation($ipAddress);


        $detect = new MobileDetect;

        // تحقق إذا كان الجهاز موبايل
        if ($detect->isMobile()) {
            $deviceType = 'Mobile';
        } else {
            $deviceType = 'Desktop';
        }

        // تحقق إذا كان الجهاز لوحي
        if ($detect->isTablet()) {
            $deviceType = 'Tablet';
        }

        // الحصول على نظام التشغيل
        $userAgent = request()->header('User-Agent');
        $os = 'Unknown OS Platform';

        if (preg_match('/windows|win32/i', $userAgent)) {
            $os = 'Windows';
        } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
            $os = 'Mac OS';
        } elseif (preg_match('/linux/i', $userAgent)) {
            $os = 'Linux';
        } elseif (preg_match('/iphone/i', $userAgent)) {
            $os = 'iOS';
        } elseif (preg_match('/android/i', $userAgent)) {
            $os = 'Android';
        }

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

        // يمكنك إرجاع استجابة أو تنفيذ مزيد من العمليات هنا
    }


    public function getVisitorCount()
    {
        $count = Visitor::count();
        return response()->json(['visitor_count' => $count]);
    }
}
