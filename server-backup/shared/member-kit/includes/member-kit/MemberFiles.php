<?php
declare(strict_types=1);

/**
 * MemberFiles -- Client for HipHop.World unified file storage API
 *
 * Thin HTTP client for consumer sites to interact with the central
 * file storage on hiphop.world. Uses file_get_contents with stream
 * context (no curl dependency).
 *
 * Usage:
 *   $files = new MemberFiles();
 *   $result = $files->upload('/tmp/song.mp3', 'audio', $bearerToken);
 *   $list   = $files->listFiles($bearerToken, 'audio');
 *   $url    = $files->getSignedUrl(42, $bearerToken);
 */
class MemberFiles
{
    private string $hubUrl;
    private string $apiKey;

    public function __construct(string $hubUrl = 'https://hiphop.world', ?string $apiKey = null)
    {
        $this->hubUrl = rtrim($hubUrl, '/');
        $this->apiKey = $apiKey ?? ($_ENV['HHW_API_KEY'] ?? '');
    }

    /**
     * Upload a file to central storage.
     *
     * @param string $filePath    Local file path to upload
     * @param string $domainKey   Domain key (e.g., 'audio', 'cards', 'documents')
     * @param string $bearerToken User's JWT / access token
     * @param array|null $metadata Optional metadata to attach
     * @return array|null  Parsed response with file info, or null on failure
     */
    public function upload(string $filePath, string $domainKey, string $bearerToken, ?array $metadata = null): ?array
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            error_log('[MemberFiles] File not found or unreadable: ' . $filePath);
            return null;
        }

        try {
            // Build multipart form data manually for file_get_contents
            $boundary = '----MemberFiles' . bin2hex(random_bytes(8));
            $body = '';

            // File field
            $filename = basename($filePath);
            $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';
            $fileContent = file_get_contents($filePath);

            $body .= "--{$boundary}\r\n";
            $body .= "Content-Disposition: form-data; name=\"file\"; filename=\"{$filename}\"\r\n";
            $body .= "Content-Type: {$mimeType}\r\n\r\n";
            $body .= $fileContent . "\r\n";

            // Domain key field
            $body .= "--{$boundary}\r\n";
            $body .= "Content-Disposition: form-data; name=\"domain_key\"\r\n\r\n";
            $body .= $domainKey . "\r\n";

            // Optional metadata field
            if ($metadata !== null) {
                $body .= "--{$boundary}\r\n";
                $body .= "Content-Disposition: form-data; name=\"metadata\"\r\n\r\n";
                $body .= json_encode($metadata, JSON_UNESCAPED_UNICODE) . "\r\n";
            }

            $body .= "--{$boundary}--\r\n";

            $headers = [
                'Content-Type: multipart/form-data; boundary=' . $boundary,
                'Authorization: Bearer ' . $bearerToken,
                'Accept: application/json',
            ];

            $context = stream_context_create([
                'http' => [
                    'method'  => 'POST',
                    'header'  => implode("\r\n", $headers),
                    'content' => $body,
                    'timeout' => 30,
                    'ignore_errors' => true,
                ],
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                ],
            ]);

            $response = @file_get_contents(
                $this->hubUrl . '/api/files/upload.php',
                false,
                $context
            );

            if ($response === false) {
                error_log('[MemberFiles] Upload request failed to: ' . $this->hubUrl);
                return null;
            }

            $decoded = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log('[MemberFiles] Invalid JSON response from upload: ' . $response);
                return null;
            }

            return $decoded;
        } catch (\Throwable $e) {
            error_log('[MemberFiles] Upload error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * List user's files from central storage.
     *
     * @param string      $bearerToken User's JWT / access token
     * @param string|null $domainKey   Optional domain filter
     * @param int         $limit       Max results (default 50)
     * @param int         $offset      Pagination offset
     * @return array|null  Parsed response with files + quota + pagination, or null on failure
     */
    public function listFiles(string $bearerToken, ?string $domainKey = null, int $limit = 50, int $offset = 0): ?array
    {
        $params = ['limit' => $limit, 'offset' => $offset];
        if ($domainKey !== null) {
            $params['domain'] = $domainKey;
        }

        $url = $this->hubUrl . '/api/files/list.php?' . http_build_query($params);

        return $this->request('GET', $url, [
            'Authorization: Bearer ' . $bearerToken,
            'Accept: application/json',
        ]);
    }

    /**
     * Get a signed URL for a file.
     *
     * This fetches the file record from the list endpoint and returns its signed URL.
     * For efficiency, if you already have the signed_url from a previous list/upload
     * response, use that directly instead of calling this method.
     *
     * @param int    $fileId      File ID
     * @param string $bearerToken User's JWT / access token
     * @return string|null  Signed URL or null on failure
     */
    public function getSignedUrl(int $fileId, string $bearerToken): ?string
    {
        // Use the list endpoint filtered to find this specific file's signed URL
        // Since the list endpoint returns signed URLs, we can search for the file
        $result = $this->listFiles($bearerToken, null, 100, 0);

        if (!$result || empty($result['files'])) {
            return null;
        }

        foreach ($result['files'] as $file) {
            if ((int) ($file['id'] ?? 0) === $fileId) {
                return $file['signed_url'] ?? null;
            }
        }

        return null;
    }

    /**
     * Make an HTTP request and return parsed JSON.
     *
     * @param string      $method   HTTP method (GET, POST, etc.)
     * @param string      $url      Full URL
     * @param array       $headers  HTTP headers
     * @param string|null $body     Request body (for POST/PUT)
     * @return array|null  Parsed JSON response or null on failure
     */
    private function request(string $method, string $url, array $headers = [], ?string $body = null): ?array
    {
        try {
            $opts = [
                'http' => [
                    'method'  => $method,
                    'header'  => implode("\r\n", $headers),
                    'timeout' => 15,
                    'ignore_errors' => true,
                ],
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                ],
            ];

            if ($body !== null) {
                $opts['http']['content'] = $body;
            }

            $context = stream_context_create($opts);
            $response = @file_get_contents($url, false, $context);

            if ($response === false) {
                error_log('[MemberFiles] Request failed: ' . $method . ' ' . $url);
                return null;
            }

            // Check HTTP status from response headers
            $httpCode = 0;
            if (!empty($http_response_header)) {
                foreach ($http_response_header as $header) {
                    if (preg_match('/^HTTP\/[\d.]+ (\d+)/', $header, $m)) {
                        $httpCode = (int) $m[1];
                    }
                }
            }

            $decoded = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log('[MemberFiles] Invalid JSON from ' . $url . ': ' . $response);
                return null;
            }

            if ($httpCode >= 400) {
                error_log('[MemberFiles] HTTP ' . $httpCode . ' from ' . $url . ': ' . $response);
            }

            return $decoded;
        } catch (\Throwable $e) {
            error_log('[MemberFiles] Request error: ' . $e->getMessage());
            return null;
        }
    }
}
