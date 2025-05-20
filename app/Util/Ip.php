<?php

declare(strict_types=1);

namespace App\Util;

/**
 * Most of the methods in this file come from symfony/http-foundation.
 * thanks provide such a useful class.
 */
class Ip
{
    public static function inIps(string $requestIp, array|string $ips): bool
    {
        $ips = (array) $ips;

        $ipv6 = substr_count($requestIp, ':') > 1;

        foreach ($ips as $ip) {
            if ($ipv6 ? self::inIp6($requestIp, $ip) : self::inIp4($requestIp, $ip)) {
                return true;
            }
        }

        return false;
    }

    public static function inIp4(string $requestIp, string $ip): bool
    {
        if (!filter_var($requestIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return false;
        }

        $address = $ip;
        $netmask = 32;

        if (str_contains($ip, '/')) {
            list($address, $netmask) = explode('/', $ip, 2);

            if ('0' === $netmask) {
                return filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
            }

            if ($netmask < 0 || $netmask > 32) {
                return false;
            }
        }

        if (false === ip2long($address)) {
            return false;
        }

        return 0 === substr_compare(sprintf('%032b', ip2long($requestIp)), sprintf('%032b', ip2long($address)), 0, intval($netmask));
    }

    /**
     * @see https://github.com/dsp/v6tools
     */
    public static function inIp6(string $requestIp, string $ip): bool
    {
        if (!((extension_loaded('sockets') && defined('AF_INET6')) || @inet_pton('::1'))) {
            return false;
        }

        $address = $ip;
        $netmask = 128;

        if (str_contains($ip, '/')) {
            list($address, $netmask) = explode('/', $ip, 2);

            if ('0' === $netmask) {
                return (bool) unpack('n*', @inet_pton($address));
            }

            if ($netmask < 1 || $netmask > 128) {
                return false;
            }
        }

        $bytesAddr = unpack('n*', @inet_pton($address));
        $bytesTest = unpack('n*', @inet_pton($requestIp));

        if (!$bytesAddr || !$bytesTest) {
            return false;
        }

        for ($i = 1, $ceil = ceil($netmask / 16); $i <= $ceil; ++$i) {
            $left = $netmask - 16 * ($i - 1);
            $left = ($left <= 16) ? $left : 16;
            $mask = ~(0xFFFF >> $left) & 0xFFFF;
            if (($bytesAddr[$i] & $mask) != ($bytesTest[$i] & $mask)) {
                return false;
            }
        }

        return true;
    }
}
