<?php

namespace App\Services;

class DeviceService
{
    /**
     * Deteksi apakah request berasal dari perangkat Mobile / Ponsel.
     */
    public static function isMobile(): bool
    {
        // 1. Cek parameter override (misal: ?device=mobile atau ?device=desktop)
        if (request()->has('device')) {
            $device = strtolower(request()->query('device'));
            if ($device === 'mobile') {
                session(['ruanggtk_device' => 'mobile']);
                return true;
            }
            if ($device === 'desktop') {
                session(['ruanggtk_device' => 'desktop']);
                return false;
            }
        }

        // 2. Cek session override
        if (session()->has('ruanggtk_device')) {
            return session('ruanggtk_device') === 'mobile';
        }

        // 3. Deteksi User-Agent
        $userAgent = request()->header('User-Agent', '');
        if (empty($userAgent)) {
            return false;
        }

        $mobileRegex = '/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos|iphone|ipad|ipod)/i';
        return (bool) preg_match($mobileRegex, $userAgent);
    }

    /**
     * Deteksi apakah request berasal dari perangkat Desktop / PC.
     */
    public static function isDesktop(): bool
    {
        return !static::isMobile();
    }

    /**
     * Render view Blade yang adaptif: memisahkan tampilan Desktop dan Mobile.
     * Jika mobile dan view mobile tersedia, render mobile view.
     * Jika tidak, render desktop view sebagai standar.
     *
     * @param string $desktopView Contoh: 'dashboard'
     * @param array $data Data variabel yang dipass ke view
     * @param string|null $mobileView Nama view mobile kustom jika ada (opsional)
     * @return \Illuminate\Contracts\View\View
     */
    public static function view(string $desktopView, array $data = [], ?string $mobileView = null)
    {
        if (static::isMobile()) {
            $targetMobile = $mobileView ?? ($desktopView . '-mobile');
            if (view()->exists($targetMobile)) {
                return view($targetMobile, $data);
            }
            // Cek alternatif folder mobile: e.g. 'mobile.dashboard'
            $folderMobile = 'mobile.' . $desktopView;
            if (view()->exists($folderMobile)) {
                return view($folderMobile, $data);
            }
        }

        return view($desktopView, $data);
    }
}
