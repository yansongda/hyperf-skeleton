<?php

declare(strict_types=1);

namespace App\Service\Http;

use App\Annotation\CacheableBreaker;
use App\Constants\ErrorCode;
use App\Exception\ApiException;
use App\Model\OAuth2\VerifyInfo;
use App\Util\Http;
use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Retry\Annotation\CircuitBreaker;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;

class OAuth2Http extends GeneralHttp
{
    public function __construct(EventDispatcherInterface $eventDispatcher, ConfigInterface $config)
    {
        parent::__construct($eventDispatcher);

        $this->client = Http::createPool([
            'base_uri' => $config->get('oauth2.url', ''),
            'timeout' => 1.0,
        ]);
    }

    /**
     * @throws ApiException
     */
    #[CacheableBreaker(ignoreThrowables: ['App\Exception\ApiException'])]
    #[Cacheable(prefix: 'oauth2:verify', ttl: 600)]
    public function getVerifyInfo(string $accessToken): VerifyInfo
    {
        try {
            $response = $this->request('get', 'oauth2/verify', [
                'headers' => [
                    'Authorization' => 'Bearer '.str_replace('Bearer ', '', $accessToken),
                ],
            ]);

            $results = json_decode((string) $response->getBody(), true);
        } catch (ApiException) {
            throw new ApiException(ErrorCode::OAUTH2_ERROR);
        }

        return new VerifyInfo($results);
    }

    /**
     * @throws ApiException
     */
    #[CircuitBreaker(maxAttempts: 1, circuitBreakerState: ['resetTimeout' => 0], fallback: 'App\Fallback\HttpServiceFallback@generalRequest')]
    public function request(string $method, string $endpoint, array $options): ResponseInterface
    {
        return parent::request(...func_get_args());
    }
}
