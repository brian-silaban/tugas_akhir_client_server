<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * HeaderKeamananFilter — Menambahkan HTTP Security Headers standar produksi
 * pada seluruh respons web Frontend JejakSumut serta menyembunyikan versi PHP.
 *
 * Kebijakan Content-Security-Policy disesuaikan agar kompatibel penuh dengan
 * Tailwind CSS CDN, Google Fonts, peta embed OpenStreetMap, dan foto destinasi.
 */
class HeaderKeamananFilter implements FilterInterface
{
    /**
     * @param array<string, mixed>|null $arguments
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        return null;
    }

    /**
     * @param array<string, mixed>|null $arguments
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Sembunyikan header versi PHP agar tidak mempermudah reconnaissance
        if (function_exists('header_remove')) {
            @header_remove('X-Powered-By');
        }
        $response->removeHeader('X-Powered-By');

        $kebijakanCsp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "font-src 'self' data: https://fonts.gstatic.com",
            "img-src 'self' data: blob: https: http:",
            "frame-src 'self' https://www.openstreetmap.org",
            "connect-src 'self'",
            "frame-ancestors 'self'",
            "base-uri 'self'",
        ]);

        $response
            ->setHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains')
            ->setHeader('Content-Security-Policy', $kebijakanCsp)
            ->setHeader('X-Frame-Options', 'SAMEORIGIN')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->setHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');

        return $response;
    }
}
