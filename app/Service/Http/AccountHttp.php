<?php

declare(strict_types=1);

namespace App\Service\Http;

use App\Annotation\CacheBreaker;
use App\Constants\ErrorCode;
use App\Exception\ApiException;
use App\Util\Logger;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Retry\Annotation\CircuitBreaker;

class AccountHttp
{
    protected array $config = [];

    protected Client $client;

    protected GeneralHttp $http;

    /**
     * Bootstrap.
     */
    public function __construct(ConfigInterface $config, GeneralHttp $http)
    {
        $this->config = $config->get('account', []);
        $this->http = $http;
        $this->client = $this->http->setBaseUri($this->config['url'] ?? '')
            ->setConnectTimeout(1.0)
            ->setTimeout(5.0)
            ->setPoolMaxIdleTime(15)
            ->getHttpClient();
    }

    /**
     * getClient.
     *
     * @author yansongda <me@yansongda.cn>
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * getUserInfo.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @Cacheable(prefix="access_token", ttl=36000)
     * @CacheBreaker(ignoreThrowables={"App\Exception\ApiException"})
     *
     * @param string $accessToken 不带 Bearer 的
     *
     * @throws \App\Exception\ApiException
     */
    public function getUserInfo(string $accessToken): array
    {
        $results = $this->requestApi('get', 'oauth2/userinfo', ['headers' => [
            'Authorization' => 'Bearer '.$accessToken,
        ]]);

        if (!isset($results['vcc_id'])) {
            throw new ApiException(ErrorCode::AUTH_FAILED);
        }

        return $results;
    }

    /**
     * 获取.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @Cacheable(prefix="access_token", value="root", ttl=86300)
     * @CacheBreaker(ignoreThrowables={"App\Exception\ApiException"})
     *
     * @throws \App\Exception\ApiException
     */
    public function getRootAccessToken(): string
    {
        $result = $this->requestApi('POST', '/oauth2/token', [
            'form_params' => [
                'client_id' => $this->config['client_id'],
                'client_secret' => $this->config['client_secret'],
                'grant_type' => 'client_credentials',
                'scope' => 'openid all_scopes',
            ],
        ]);

        if (isset($result['access_token'])) {
            return $result['access_token'];
        }

        throw new ApiException(ErrorCode::ACCOUNT_ERROR);
    }

    /**
     * requestApi.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @CircuitBreaker(
     *     maxAttempts=3,
     *     circuitBreakerState={"resetTimeout": 0},
     *     fallback={"App\Fallback\HttpServiceFallback@accountRequestApi"}
     * )
     *
     * @throws \App\Exception\ApiException
     */
    protected function requestApi(string $method, string $endpoint, array $options): array
    {
        Logger::info('[AccountHttp] 请求用户中心接口', func_get_args());

        try {
            $startTime = microtime(true);

            $response = $this->getClient()->request($method, $endpoint, $options)->getBody()->getContents();

            $result = is_array($response) ? $response : json_decode($response, true);

            Logger::info('[AccountHttp] 请求用户中心接口结果', ['time' => microtime(true) - $startTime, 'response' => $response]);

            if (is_array($result)) {
                return $result;
            }

            Logger::warning('[AccountHttp] 请求用户中心结果不正确', array_merge(func_get_args(), ['result' => $response]));

            return [];
        } catch (ClientException $e) {
            Logger::warning('[AccountHttp] 请求用户中心结果不正确:4xx 错误即，token 无效', func_get_args());
            // 4xx 错误即，token 无效
            return [];
        } catch (GuzzleException $e) {
            Logger::error('[AccountHttp] 请求用户中心失败', [$e->getMessage()]);
        }

        throw new ApiException(ErrorCode::ACCOUNT_ERROR);
    }
}
