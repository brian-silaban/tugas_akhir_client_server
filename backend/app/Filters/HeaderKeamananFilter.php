<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * HeaderKeamananFilter — Menambahkan HTTP Security Headers ketat khusus
 * layanan REST API JSON pada Backend JejakSumut serta menghapus X-Powered-By.
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
        if (function_exists('header_remove')) {
            @header_remove('X-Powered-By');
        }
        $response->removeHeader('X-Powered-By');

        $response
            ->setHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains')
            ->setHeader('Content-Security-Policy', "default-src 'none'; frame-ancestors 'none'")
            ->setHeader('X-Frame-Options', 'DENY')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setHeader('Referrer-Policy', 'no-referrer')
            ->setHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');

        return $response;
    }
}
