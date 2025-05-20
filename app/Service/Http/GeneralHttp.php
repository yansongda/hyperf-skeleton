<?php

declare(strict_types=1);

namespace App\Service\Http;

use App\Constants\ErrorCode;
use App\Event\Metric;
use App\Exception\ApiException;
use App\Util\Http;
use App\Util\Logger;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

use function App\get_request_id;

class GeneralHttp
{
    protected ?Client $client = null;

    public function __construct(protected EventDispatcherInterface $eventDispatcher) {}

    /**
     * @throws ApiException
     */
    public function request(string $method, string $endpoint, array $options): ResponseInterface
    {
        Logger::info('[GeneralHttp] 请求第三方服务接口', array_merge([get_class($this)], func_get_args()));

        $startTime = microtime(true);

        try {
            $client = $this->client ?? Http::create();

            $response = $client->request($method, $endpoint, array_merge_recursive($options, [
                'headers' => [
                    'x-request-id' => get_request_id(),
                ],
            ]));
        } catch (RequestException $e) {
            $errorMessage = $e->getMessage();
            $response = $e->hasResponse() ? $e->getResponse() : null;
        } catch (Throwable $e) {
            $errorMessage = $e->getMessage();
        }

        $duration = microtime(true) - $startTime;

        if (self::class !== get_class($this)) {
            $this->eventDispatcher->dispatch(new Metric(new \App\Model\Metric\Http([
                'class' => get_class($this),
                'endpoint' => $endpoint,
                'duration' => $duration,
            ])));
        }

        if (isset($response)) {
            Logger::info('[GeneralHttp] 请求第三方服务接口结果', ['time' => $duration, 'response' => (string) $response->getBody()]);

            return $response;
        }

        Logger::warning('[GeneralHttp] 请求第三方服务接口失败', ['errorMessage' => $errorMessage ?? null]);

        throw new ApiException(ErrorCode::THIRD_API_ERROR, $errorMessage ?? null);
    }
}
