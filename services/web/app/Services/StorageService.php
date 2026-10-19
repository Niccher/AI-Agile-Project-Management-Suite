<?php

namespace App\Services;

/**
 * StorageService
 *
 * High-performance, self-contained S3-compatible object storage service for MinIO.
 * Implements AWS Signature Version 4 (AWS4-HMAC-SHA256) natively without heavy external
 * dependencies. Supports uploads, streaming downloads, bucket auto-creation, and
 * client-facing pre-signed URL generation with local filesystem fallback.
 */
class StorageService
{
    protected string $endpoint;
    protected string $publicEndpoint;
    protected string $accessKey;
    protected string $secretKey;
    protected string $region;
    protected bool $directPresigned;
    protected bool $enabled;

    public function __construct()
    {
        $this->endpoint        = rtrim(env('S3_ENDPOINT', 'http://minio:9000'), '/');
        $this->publicEndpoint  = rtrim(env('S3_PUBLIC_ENDPOINT', 'http://localhost:9000'), '/');
        $this->accessKey       = (string) env('S3_ACCESS_KEY', 'minio_admin');
        $this->secretKey       = (string) env('S3_SECRET_KEY', 'minio_super_secret_2026');
        $this->region          = (string) env('S3_REGION', 'us-east-1');
        $this->directPresigned = filter_var(env('S3_DIRECT_PRESIGNED', true), FILTER_VALIDATE_BOOLEAN);
        $this->enabled         = !empty($this->endpoint) && !empty($this->accessKey);
    }

    /**
     * Check if S3 object storage is configured and enabled.
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Check if client-facing pre-signed direct URLs should be used.
     */
    public function useDirectPresigned(): bool
    {
        return $this->directPresigned;
    }

    /**
     * Upload a file from a local filesystem path to an S3 bucket.
     */
    public function uploadFile(string $bucket, string $key, string $localFilePath, string $mimeType = 'application/octet-stream'): bool
    {
        if (!file_exists($localFilePath)) {
            return false;
        }

        $contents = file_get_contents($localFilePath);
        if ($contents === false) {
            return false;
        }

        return $this->uploadContents($bucket, $key, $contents, $mimeType);
    }

    /**
     * Upload raw binary or text contents to an S3 bucket.
     */
    public function uploadContents(string $bucket, string $key, string $contents, string $mimeType = 'application/octet-stream'): bool
    {
        $key = ltrim($key, '/');
        $uri = "/{$bucket}/{$key}";

        $headers = [
            'Content-Type' => $mimeType,
        ];

        $response = $this->sendRequest('PUT', $uri, [], $headers, $contents);
        return $response['status_code'] >= 200 && $response['status_code'] < 300;
    }

    /**
     * Retrieve an object from S3. Returns array with body, mime_type, and size or null if not found.
     *
     * @return array{body: string, mime_type: string, size: int}|null
     */
    public function getObject(string $bucket, string $key): ?array
    {
        $key = ltrim($key, '/');
        $uri = "/{$bucket}/{$key}";

        $response = $this->sendRequest('GET', $uri);
        if ($response['status_code'] !== 200) {
            return null;
        }

        return [
            'body'      => $response['body'],
            'mime_type' => $response['headers']['content-type'] ?? 'application/octet-stream',
            'size'      => strlen($response['body']),
        ];
    }

    /**
     * Check if an object exists in an S3 bucket.
     */
    public function hasObject(string $bucket, string $key): bool
    {
        $key = ltrim($key, '/');
        $uri = "/{$bucket}/{$key}";

        $response = $this->sendRequest('HEAD', $uri);
        return $response['status_code'] === 200;
    }

    /**
     * Delete an object from an S3 bucket.
     */
    public function deleteObject(string $bucket, string $key): bool
    {
        $key = ltrim($key, '/');
        $uri = "/{$bucket}/{$key}";

        $response = $this->sendRequest('DELETE', $uri);
        return in_array($response['status_code'], [200, 204], true);
    }

    /**
     * Ensure a bucket exists, creating it if necessary.
     */
    public function ensureBucket(string $bucket): bool
    {
        $response = $this->sendRequest('HEAD', "/{$bucket}");
        if ($response['status_code'] === 200) {
            return true;
        }

        $createResponse = $this->sendRequest('PUT', "/{$bucket}");
        return in_array($createResponse['status_code'], [200, 409], true);
    }

    /**
     * Generate an S3 pre-signed GET URL valid for a specified duration.
     * Uses S3_PUBLIC_ENDPOINT so user browsers can access MinIO directly.
     */
    public function getPresignedUrl(string $bucket, string $key, int $expirySeconds = 900, string $downloadName = ''): string
    {
        $key = ltrim($key, '/');
        $uri = "/{$bucket}/{$key}";

        $parsedUrl = parse_url($this->publicEndpoint);
        $host = $parsedUrl['host'] . (isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '');
        $scheme = $parsedUrl['scheme'] ?? 'http';

        $now = time();
        $date = gmdate('Ymd', $now);
        $datetime = gmdate('Ymd\THis\Z', $now);

        $credentialScope = "{$date}/{$this->region}/s3/aws4_request";

        $queryParams = [
            'X-Amz-Algorithm'     => 'AWS4-HMAC-SHA256',
            'X-Amz-Credential'    => "{$this->accessKey}/{$credentialScope}",
            'X-Amz-Date'          => $datetime,
            'X-Amz-Expires'       => (string) $expirySeconds,
            'X-Amz-SignedHeaders' => 'host',
        ];

        if (!empty($downloadName)) {
            $queryParams['response-content-disposition'] = 'inline; filename="' . addslashes($downloadName) . '"';
        }

        ksort($queryParams);

        $canonicalQuery = [];
        foreach ($queryParams as $k => $v) {
            $canonicalQuery[] = rawurlencode($k) . '=' . rawurlencode($v);
        }
        $canonicalQueryString = implode('&', $canonicalQuery);

        $canonicalHeaders = "host:{$host}\n";
        $signedHeaders = 'host';
        $payloadHash = 'UNSIGNED-PAYLOAD';

        $canonicalRequest = "GET\n{$uri}\n{$canonicalQueryString}\n{$canonicalHeaders}\n{$signedHeaders}\n{$payloadHash}";

        $stringToSign = "AWS4-HMAC-SHA256\n{$datetime}\n{$credentialScope}\n" . hash('sha256', $canonicalRequest);
        $signingKey = $this->getSigningKey($this->secretKey, $date, $this->region, 's3');
        $signature = hash_hmac('sha256', $stringToSign, $signingKey);

        return "{$scheme}://{$host}{$uri}?{$canonicalQueryString}&X-Amz-Signature={$signature}";
    }

    /**
     * Send an authenticated AWS4-HMAC-SHA256 HTTP request to the internal MinIO endpoint.
     */
    protected function sendRequest(string $method, string $uri, array $queryParams = [], array $headers = [], string $payload = ''): array
    {
        $parsedUrl = parse_url($this->endpoint);
        $host = $parsedUrl['host'] . (isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '');
        $scheme = $parsedUrl['scheme'] ?? 'http';

        $now = time();
        $date = gmdate('Ymd', $now);
        $datetime = gmdate('Ymd\THis\Z', $now);

        $payloadHash = hash('sha256', $payload);

        $headers['Host'] = $host;
        $headers['x-amz-date'] = $datetime;
        $headers['x-amz-content-sha256'] = $payloadHash;

        // Canonical Query String
        ksort($queryParams);
        $canonicalQuery = [];
        foreach ($queryParams as $k => $v) {
            $canonicalQuery[] = rawurlencode($k) . '=' . rawurlencode($v);
        }
        $canonicalQueryString = implode('&', $canonicalQuery);

        // Canonical Headers
        $canonicalHeadersArr = [];
        $signedHeadersArr = [];
        foreach ($headers as $k => $v) {
            $lowerKey = strtolower($k);
            $canonicalHeadersArr[$lowerKey] = trim($v);
            $signedHeadersArr[] = $lowerKey;
        }
        ksort($canonicalHeadersArr);
        sort($signedHeadersArr);

        $canonicalHeaders = '';
        foreach ($canonicalHeadersArr as $k => $v) {
            $canonicalHeaders .= "{$k}:{$v}\n";
        }
        $signedHeaders = implode(';', $signedHeadersArr);

        // Canonical Request
        $canonicalRequest = "{$method}\n{$uri}\n{$canonicalQueryString}\n{$canonicalHeaders}\n{$signedHeaders}\n{$payloadHash}";

        // String to sign
        $credentialScope = "{$date}/{$this->region}/s3/aws4_request";
        $stringToSign = "AWS4-HMAC-SHA256\n{$datetime}\n{$credentialScope}\n" . hash('sha256', $canonicalRequest);

        // Compute Signature
        $signingKey = $this->getSigningKey($this->secretKey, $date, $this->region, 's3');
        $signature = hash_hmac('sha256', $stringToSign, $signingKey);

        $authHeader = "AWS4-HMAC-SHA256 Credential={$this->accessKey}/{$credentialScope}, SignedHeaders={$signedHeaders}, Signature={$signature}";
        $headers['Authorization'] = $authHeader;

        // Execute cURL
        $url = "{$scheme}://{$host}{$uri}" . ($canonicalQueryString ? "?{$canonicalQueryString}" : '');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        if (!empty($payload)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        }

        $formattedHeaders = [];
        foreach ($headers as $k => $v) {
            $formattedHeaders[] = "{$k}: {$v}";
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $formattedHeaders);

        $rawResponse = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        if ($rawResponse === false) {
            return [
                'status_code' => 0,
                'headers'     => [],
                'body'        => '',
            ];
        }

        $responseHeadersRaw = substr($rawResponse, 0, $headerSize);
        $responseBody = substr($rawResponse, $headerSize);

        $parsedHeaders = [];
        foreach (explode("\r\n", $responseHeadersRaw) as $line) {
            if (strpos($line, ': ') !== false) {
                [$hk, $hv] = explode(': ', $line, 2);
                $parsedHeaders[strtolower($hk)] = $hv;
            }
        }

        return [
            'status_code' => $httpCode,
            'headers'     => $parsedHeaders,
            'body'        => $responseBody,
        ];
    }

    /**
     * Compute AWS4 signing key.
     */
    protected function getSigningKey(string $key, string $date, string $region, string $service): string
    {
        $kDate    = hash_hmac('sha256', $date, 'AWS4' . $key, true);
        $kRegion  = hash_hmac('sha256', $region, $kDate, true);
        $kService = hash_hmac('sha256', $service, $kRegion, true);
        return hash_hmac('sha256', 'aws4_request', $kService, true);
    }
}
