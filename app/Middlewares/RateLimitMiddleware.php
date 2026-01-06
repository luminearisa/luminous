<?php

namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;

/**
 * Rate Limit Middleware
 * Simple file-based rate limiting per IP address
 */
class RateLimitMiddleware
{
    private string $cachePath;
    private string $configPath;

    public function __construct()
    {
        $this->cachePath = __DIR__ . '/../../storage/cache';
        $this->configPath = __DIR__ . '/../../config/rate.lumi';
    }

    public function handle(Request $request, Response $response): bool
    {
        // Load configuration
        $config = $this->loadConfig();

        // If rate limiting is disabled, allow request
        if (!$config['enabled']) {
            return true;
        }

        // Get client IP
        $ip = $this->getClientIp($request);

        // Get rate limit for this IP
        $rateData = $this->getRateData($ip);

        // Check if limit exceeded
        if ($this->isLimitExceeded($rateData, $config['limit_per_minute'])) {
            $this->sendRateLimitResponse($response, $rateData);
            return false;
        }

        // Update rate data
        $this->updateRateData($ip, $rateData);

        // Add rate limit headers
        $this->addRateLimitHeaders($rateData, $config['limit_per_minute']);

        return true;
    }

    /**
     * Load rate limit configuration
     */
    private function loadConfig(): array
    {
        if (!file_exists($this->configPath)) {
            return ['enabled' => false, 'limit_per_minute' => 60];
        }

        $content = file_get_contents($this->configPath);
        $config = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['enabled' => false, 'limit_per_minute' => 60];
        }

        return $config;
    }

    /**
     * Get client IP address
     */
    private function getClientIp(Request $request): string
    {
        // Check for proxy headers
        $ip = $request->header('X-Forwarded-For');
        if ($ip) {
            $ips = explode(',', $ip);
            return trim($ips[0]);
        }

        $ip = $request->header('X-Real-IP');
        if ($ip) {
            return $ip;
        }

        return $request->ip();
    }

    /**
     * Get rate data for IP
     */
    private function getRateData(string $ip): array
    {
        $filename = $this->getRateFilename($ip);

        if (!file_exists($filename)) {
            return [
                'count' => 0,
                'reset_time' => time() + 60
            ];
        }

        $content = file_get_contents($filename);
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'count' => 0,
                'reset_time' => time() + 60
            ];
        }

        // Check if window has expired
        if (time() >= $data['reset_time']) {
            return [
                'count' => 0,
                'reset_time' => time() + 60
            ];
        }

        return $data;
    }

    /**
     * Update rate data for IP
     */
    private function updateRateData(string $ip, array $rateData): void
    {
        $filename = $this->getRateFilename($ip);

        // Increment count
        $rateData['count']++;

        // Ensure cache directory exists
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }

        // Save to file
        file_put_contents($filename, json_encode($rateData));
    }

    /**
     * Check if rate limit is exceeded
     */
    private function isLimitExceeded(array $rateData, int $limit): bool
    {
        return $rateData['count'] >= $limit;
    }

    /**
     * Send rate limit exceeded response
     */
    private function sendRateLimitResponse(Response $response, array $rateData): void
    {
        $retryAfter = $rateData['reset_time'] - time();

        http_response_code(429);
        header("Retry-After: $retryAfter");
        header('X-RateLimit-Limit: ' . ($rateData['count'] + 1));
        header('X-RateLimit-Remaining: 0');
        header('X-RateLimit-Reset: ' . $rateData['reset_time']);
        header('Content-Type: application/json');

        echo json_encode([
            'status' => 'error',
            'message' => 'Too many requests. Please try again later.',
            'retry_after' => $retryAfter
        ]);

        exit;
    }

    /**
     * Add rate limit headers to response
     */
    private function addRateLimitHeaders(array $rateData, int $limit): void
    {
        $remaining = max(0, $limit - $rateData['count']);

        header('X-RateLimit-Limit: ' . $limit);
        header('X-RateLimit-Remaining: ' . $remaining);
        header('X-RateLimit-Reset: ' . $rateData['reset_time']);
    }

    /**
     * Get rate limit filename for IP
     */
    private function getRateFilename(string $ip): string
    {
        $safeIp = str_replace(['.', ':'], '_', $ip);
        return $this->cachePath . "/rate_limit_$safeIp.json";
    }

    /**
     * Clean old rate limit files (optional cleanup)
     */
    public static function cleanup(): void
    {
        $cachePath = __DIR__ . '/../../storage/cache';
        
        if (!is_dir($cachePath)) {
            return;
        }

        $files = glob($cachePath . '/rate_limit_*.json');
        $now = time();

        foreach ($files as $file) {
            $content = @file_get_contents($file);
            if ($content === false) {
                continue;
            }

            $data = json_decode($content, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                // Delete if expired for more than 5 minutes
                if (isset($data['reset_time']) && $now > $data['reset_time'] + 300) {
                    @unlink($file);
                }
            }
        }
    }
}
