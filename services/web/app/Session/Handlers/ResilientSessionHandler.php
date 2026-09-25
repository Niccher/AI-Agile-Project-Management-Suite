<?php

namespace App\Session\Handlers;

use CodeIgniter\Session\Handlers\BaseHandler;
use CodeIgniter\Session\Handlers\DatabaseHandler;
use CodeIgniter\Session\Handlers\RedisHandler;
use Config\Session as SessionConfig;
use SessionHandlerInterface;

/**
 * ResilientSessionHandler
 * 
 * Attempts to use Redis for session storage. If the Redis server is 
 * unreachable or fails, it seamlessly falls back to the DatabaseHandler 
 * to ensure uninterrupted user sessions.
 */
class ResilientSessionHandler extends BaseHandler implements SessionHandlerInterface
{
    /**
     * The active session handler instance.
     * @var SessionHandlerInterface
     */
    protected $activeHandler;

    /**
     * @var SessionConfig
     */
    protected $config;

    /**
     * @var string
     */
    protected $ipAddress;

    public function __construct(SessionConfig $config, string $ipAddress)
    {
        parent::__construct($config, $ipAddress);
        $this->config = $config;
        $this->ipAddress = $ipAddress;
    }

    /**
     * Initialize the appropriate handler on open.
     */
    public function open($path, $name): bool
    {
        try {
            // Attempt to use Redis Handler first
            $redisConfig = clone $this->config;
            
            // Auto-detect Railway or local Redis environment
            $redisUrl = getenv('REDIS_URL');
            if ($redisUrl) {
                // If Railway provides REDIS_URL like redis://default:pass@host:port
                // CodeIgniter RedisHandler expects tcp://host:port?auth=pass
                $parsed = parse_url($redisUrl);
                $host = $parsed['host'] ?? 'redis';
                $port = $parsed['port'] ?? 6379;
                $pass = $parsed['pass'] ?? '';
                
                $pathStr = "tcp://{$host}:{$port}";
                if ($pass) {
                    $pathStr .= "?auth=" . $pass;
                }
                $redisConfig->savePath = $pathStr;
            } else {
                $redisConfig->savePath = 'tcp://redis:6379';
            }
            
            $this->activeHandler = new RedisHandler($redisConfig, $this->ipAddress);
            
            // Try to open the Redis connection
            $opened = $this->activeHandler->open($redisConfig->savePath, $name);
            
            if (!$opened) {
                throw new \Exception("Failed to open Redis session connection.");
            }
            
            return true;
            
        } catch (\Exception | \Throwable $e) {
            // Fallback to Database Handler
            log_message('warning', 'Redis session failed: ' . $e->getMessage() . '. Falling back to DatabaseHandler.');
            
            $dbConfig = clone $this->config;
            $dbConfig->savePath = 'ci_sessions'; // Your database table name
            
            $this->activeHandler = new DatabaseHandler($dbConfig, $this->ipAddress);
            return $this->activeHandler->open($dbConfig->savePath, $name);
        }
    }

    public function close(): bool
    {
        return $this->activeHandler ? $this->activeHandler->close() : true;
    }

    public function read($id): string|false
    {
        return $this->activeHandler ? $this->activeHandler->read($id) : false;
    }

    public function write($id, $data): bool
    {
        return $this->activeHandler ? $this->activeHandler->write($id, $data) : false;
    }

    public function destroy($id): bool
    {
        return $this->activeHandler ? $this->activeHandler->destroy($id) : false;
    }

    public function gc($max_lifetime): int|false
    {
        return $this->activeHandler ? $this->activeHandler->gc($max_lifetime) : false;
    }
}
