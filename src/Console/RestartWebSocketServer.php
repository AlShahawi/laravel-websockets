<?php

namespace BeyondCode\LaravelWebSockets\Console;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use React\Socket\Connector;
use Clue\React\Buzz\Browser;
use Illuminate\Console\Command;
use React\Dns\Config\Config as DnsConfig;
use React\EventLoop\Factory as LoopFactory;
use React\Dns\Resolver\Factory as DnsFactory;
use React\Dns\Resolver\Resolver as ReactDnsResolver;
use BeyondCode\LaravelWebSockets\Statistics\DnsResolver;
use BeyondCode\LaravelWebSockets\Facades\StatisticsLogger;
use BeyondCode\LaravelWebSockets\Facades\WebSocketsRouter;
use BeyondCode\LaravelWebSockets\Server\Logger\HttpLogger;
use BeyondCode\LaravelWebSockets\Server\WebSocketServerFactory;
use BeyondCode\LaravelWebSockets\Server\Logger\ConnectionLogger;
use BeyondCode\LaravelWebSockets\Server\Logger\WebsocketsLogger;
use BeyondCode\LaravelWebSockets\WebSockets\Channels\ChannelManager;
use BeyondCode\LaravelWebSockets\Statistics\Logger\HttpStatisticsLogger;
use BeyondCode\LaravelWebSockets\Statistics\Logger\StatisticsLogger as StatisticsLoggerInterface;

class RestartWebSocketServer extends Command
{
    protected $signature = 'websockets:restart';

    protected $description = 'Restart the Laravel WebSocket Server';

    /**
     * @var CacheRepository
     */
    private $cache;

    public function __construct(CacheRepository $cache)
    {
        parent::__construct();

        $this->cache = $cache;
    }

    public function handle()
    {
        $processId = $this->cache->pull('websockets:process_id');

        $this->warn("Sending a kill signal to process $processId...");

        if (!$processId) {
            $this->error('The process is not running.');
            return;
        } elseif (posix_kill($processId, SIGKILL)) {
            $this->info('Process killed.');
        } else {
            $this->error('Failed to kill. it may be already stopped.');
        }
    }
}
