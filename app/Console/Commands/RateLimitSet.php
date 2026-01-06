<?php

namespace App\Console\Commands;

/**
 * Rate Limit Set Command
 * Set rate limit configuration
 */
class RateLimitSet extends Command
{
    private string $configPath;

    public function __construct()
    {
        $this->configPath = __DIR__ . '/../../../config/rate.lumi';
    }

    public function execute(array $arguments): void
    {
        $value = $this->argument($arguments, 0);

        if (!$value) {
            $this->error('Limit value is required');
            $this->info('Usage: php lumi limit:set <value>');
            $this->info('');
            $this->info('Examples:');
            $this->info('  php lumi limit:set 60        # 60 requests per minute');
            $this->info('  php lumi limit:set 600       # 600 requests per minute');
            $this->info('  php lumi limit:set unlimited # Disable rate limiting');
            return;
        }

        try {
            if (strtolower($value) === 'unlimited') {
                $this->setUnlimited();
            } else {
                $this->setLimit($value);
            }
        } catch (\Exception $e) {
            $this->error('Failed to update rate limit: ' . $e->getMessage());
        }
    }

    /**
     * Set numeric limit
     */
    private function setLimit(string $value): void
    {
        // Validate numeric value
        if (!is_numeric($value) || (int)$value <= 0) {
            $this->error('Limit must be a positive number');
            return;
        }

        $limit = (int)$value;

        $config = [
            'enabled' => true,
            'limit_per_minute' => $limit
        ];

        $this->saveConfig($config);

        $this->success("Rate limit set to: $limit requests per minute");
        $this->info('Rate limiting is now enabled');
        $this->info('');
        $this->info('To disable: php lumi limit:set unlimited');
    }

    /**
     * Disable rate limiting
     */
    private function setUnlimited(): void
    {
        $config = [
            'enabled' => false,
            'limit_per_minute' => 0
        ];

        $this->saveConfig($config);

        $this->success('Rate limiting disabled');
        $this->warning('⚠ Your API now has no rate limiting');
        $this->info('');
        $this->info('To enable: php lumi limit:set <number>');
    }

    /**
     * Save configuration to file
     */
    private function saveConfig(array $config): void
    {
        $json = json_encode($config, JSON_PRETTY_PRINT);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Failed to encode config: ' . json_last_error_msg());
        }

        // Ensure config directory exists
        $dir = dirname($this->configPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $result = file_put_contents($this->configPath, $json);

        if ($result === false) {
            throw new \RuntimeException('Failed to write config file');
        }
    }

    /**
     * Load current configuration
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
}
