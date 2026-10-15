<?php

namespace Wallabag\Helper;

use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;

/** Bounded, credential-free HTTP with DNS pinning on every redirect. */
class PublicHttpClient
{
    public static function normalize(string $base, string $url): string
    {
        return (string) UriResolver::resolve(new Uri($base), new Uri($url))->withFragment('');
    }

    public function validate(string $url): array
    {
        $parts = parse_url($url);
        if (!$parts || !in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)
            || empty($parts['host']) || isset($parts['user']) || isset($parts['pass'])
            || strlen($url) > 2048 || preg_match('/[\x00-\x20\x7f]/', $url)
            || (isset($parts['port']) && !in_array($parts['port'], [80, 443], true))) {
            throw new \InvalidArgumentException('Use a public HTTP(S) URL without credentials on port 80 or 443.');
        }
        $host = trim($parts['host'], '[]');
        $ips = filter_var($host, FILTER_VALIDATE_IP) ? [$host] : $this->resolve($host);
        if (!$ips) {
            throw new \InvalidArgumentException('URL host could not be resolved.');
        }
        foreach ($ips as $ip) {
            if (!self::isPublicIp($ip)) {
                throw new \InvalidArgumentException('Private, local, reserved and metadata addresses are blocked.');
            }
        }

        return $ips;
    }

    public static function isPublicIp(string $ip): bool
    {
        // Accept only global unicast IPv6; conservatively exclude transition/special-use ranges.
        if (strpos($ip, ':') !== false) {
            $packed = @inet_pton($ip);
            if (false === $packed || 16 !== strlen($packed)) { return false; }
            $prefix = unpack('nfirst/nsecond', substr($packed, 0, 4));
            if (($prefix['first'] & 0xe000) !== 0x2000 || 0x2002 === $prefix['first']
                || (0x2001 === $prefix['first'] && ($prefix['second'] <= 0x1ff || 0xdb8 === $prefix['second']))
                || 0x3fff === $prefix['first']) { return false; }
        }

        return false !== filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)
            && !preg_match('/^(0\.|127\.|100\.(6[4-9]|[7-9]\d|1[01]\d|12[0-7])\.|169\.254\.|192\.0\.|192\.88\.99\.|198\.(18|19)\.|198\.51\.100\.|203\.0\.113\.|2(?:2[4-9]|[3-5]\d)\.)/', $ip);
    }

    protected function resolve(string $host): array
    {
        $records = @dns_get_record($host, DNS_A | DNS_AAAA);

        return array_values(array_filter(array_map(static function ($r) { return $r['ip'] ?? $r['ipv6'] ?? null; }, $records ?: [])));
    }

    /** @return array{url:string,body:string,status:int} */
    public function get(string $url): array
    {
        for ($redirect = 0; $redirect <= 5; ++$redirect) {
            $ips = $this->validate($url);
            $response = $this->request($url, $ips);
            $status = $response['status'];
            $body = $response['body'];
            $location = $response['location'];
            if ($status >= 300 && $status < 400 && $location) {
                $url = self::normalize($url, $location);
                continue;
            }
            if ($status < 200 || $status >= 300) {
                throw new \RuntimeException('Source returned HTTP ' . $status . '.');
            }

            return ['url' => $url, 'body' => $body, 'status' => $status];
        }
        throw new \RuntimeException('Too many redirects.');
    }

    /** Separate transport boundary allows offline redirect/rebinding regression tests. */
    protected function request(string $url, array $ips): array
    {
        $parts = parse_url($url);
        $port = $parts['port'] ?? ('https' === strtolower($parts['scheme']) ? 443 : 80);
        $host = trim($parts['host'], '[]');
        $ip = strpos($ips[0], ':') === false ? $ips[0] : '[' . $ips[0] . ']';
        $body = '';
        $location = null;
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_PROXY => '',
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_RESOLVE => [$host . ':' . $port . ':' . $ip],
            CURLOPT_USERAGENT => 'wallabag RSS/X importer',
            CURLOPT_HTTPHEADER => ['Accept: application/atom+xml, application/rss+xml, application/xml, text/html, application/json'],
            CURLOPT_HEADERFUNCTION => static function ($ch, $line) use (&$location) {
                if (0 === stripos($line, 'Location:')) {
                    $location = trim(substr($line, 9));
                }
                return strlen($line);
            },
            CURLOPT_WRITEFUNCTION => static function ($ch, $chunk) use (&$body) {
                if (strlen($body) + strlen($chunk) > 2 * 1024 * 1024) {
                    return 0;
                }
                $body .= $chunk;
                return strlen($chunk);
            },
        ]);
        $ok = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if (false === $ok) {
            throw new \RuntimeException('Fetch failed (timeout, TLS, network or 2 MiB size limit).');
        }
        return ['body' => $body, 'status' => $status, 'location' => $location];
    }

}
