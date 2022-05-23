<?php

declare(strict_types=1);

namespace App\Service\Http;

use App\Annotation\CacheableBreaker;
use App\Constants\ErrorCode;
use App\Exception\ApiException;
use App\Util\Http;
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

    /**
     * Bootstrap.
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config->get('account', []);
        $this->client = Http::createPool([
            'base_uri' => $this->config['url'] ?? '',
            'timeout' => 2.0,
        ]);
    }

    /**
     * getUserInfo.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @Cacheable(prefix="access_token", ttl=36000)
     * @CacheableBreaker(ignoreThrowables={"App\Exception\ApiException"})
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

        // 坐席id、角色、部门需强转为int类型
        $results['user_id'] = intval($results['user_id']);
        $results['dept_id'] = intval($results['dept_id']);
        $results['role_id'] = intval($results['role_id']);
        $results['access_token'] = $accessToken;

        return $results;
    }

    /**
     * requestApi.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @CircuitBreaker(
     *     maxAttempts=1,
     *     circuitBreakerState={"resetTimeout": 0},
     *     fallback="App\Fallback\HttpServiceFallback@accountRequestApi"
     * )
     *
     * @throws \App\Exception\ApiException
     */
    protected function requestApi(string $method, string $endpoint, array $options): array
    {
        Logger::info('[AccountHttp] 请求用户中心接口', func_get_args());

        try {
            $startTime = microtime(true);

            $response = $this->client->request($method, $endpoint, $options)->getBody()->getContents();

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
