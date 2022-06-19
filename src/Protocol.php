<?php

declare(strict_types = 1);

namespace Plattry\Http;

use Plattry\Http\Foundation\HttpFactory;
use Plattry\Network\Connection\ConnectionInterface;
use Plattry\Network\Protocol\Http;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Protocol
 * @package Plattry\Http
 */
class Protocol extends Http
{
    /**
     * HttpFactory instance
     * @var HttpFactory
     */
    protected HttpFactory $factory;

    /**
     * Protocol constructor.
     */
    public function __construct()
    {
        $this->factory = new HttpFactory();
    }

    /**
     * Get request from connection.
     * @param ConnectionInterface $conn
     * @return ServerRequestInterface
     */
    public function getRequestFromConnection(ConnectionInterface $conn): ServerRequestInterface
    {
        $raw = $conn->receive();
        $envs = $conn->getAttribute();
        [$method, $target, $version, $headers, $rawBody, $parsedBody, $files] = $this->detachRequestBag($raw);

        $request = $this->factory->createServerRequest($method, $target, $envs)
            ->withProtocolVersion($version)->withHeaders($headers)->withParsedBody($parsedBody);

        $request->getBody()->write($rawBody);
        $request->getBody()->rewind();

        parse_str(str_replace('; ', '&', $headers['cookie'] ?? ''), $cookies);
        $request->withCookieParams($cookies);

        parse_str((string)parse_url($target, PHP_URL_QUERY), $query);
        $request->withQueryParams($query);

        array_walk($files, function (&$val) {
            $stream = $this->factory->createStreamFromFile($val['tmp_name']);
            $val = $this->factory->createUploadedFile(
                $stream, $val['size'], $val['error'], $val['name'], $val['type']
            );
        });
        $request->withUploadedFiles($files);

        return $request;
    }

    /**
     * Back response to connection.
     * @param ConnectionInterface $conn
     * @param ResponseInterface $response
     * @return void
     */
    public function backResponseToConnection(ConnectionInterface $conn, ResponseInterface $response): void
    {
        $version = $response->getProtocolVersion();
        $code = $response->getStatusCode();
        $phrase = $response->getReasonPhrase();
        $headers = $response->getHeaders();
        $body = $response->getBody()->getContents();

        $raw = $this->compileResponseBag($version, $code, $phrase, $headers, $body);
        $conn->send($raw);
    }

    /**
     * Detach request data bag from raw.
     * @param string $raw
     * @return array
     */
    protected function detachRequestBag(string $raw): array
    {
        // Get the raw header and body.
        $hbSplitPos = strpos($raw, "\r\n\r\n");
        $rawHeader = substr($raw, 0, $hbSplitPos);
        $rawBody = substr($raw, $hbSplitPos + 4);

        // Get the raw first line and other lines.
        $foSplitPos = strpos($raw, "\r\n");
        $firstLine = substr($rawHeader, 0, $foSplitPos);
        $otherLine = substr($rawHeader, $foSplitPos + 2);

        // Get the method, target and protocol version from first line.
        $firstLineArr = explode(' ', $firstLine, 3);
        $method = $firstLineArr[0];
        $target = $firstLineArr[1] ?? '/';
        $version = substr($firstLineArr[2] ?? '', 5) ?: '1.0';

        // Get header data from the order lines.
        $headers = [];
        foreach (explode("\r\n", $otherLine) as $line) {
            [$key, $value] = explode(':', $line, 2);
            $headers[strtolower(trim($key))] = trim($value);
        }

        // Return data if no body.
        if (empty($rawBody)) {
            return [$method, $target, $version, $headers, $rawBody, [], []];
        }

        // Get parsed body data and uploaded files from raw body.
        [$parsedBody, $files] = $this->parseBody($headers['content-type'] ?? '', $rawBody);

        return [$method, $target, $version, $headers, $rawBody, $parsedBody, $files];
    }

    /**
     * Parse Body by Content-Type.
     * @param string $contentType
     * @param string $rawBody
     * @return array[]
     */
    protected function parseBody(string $contentType, string $rawBody): array
    {
        $parsedBody = $files = [];
        if (str_contains($contentType, 'json')) {
            $parsedBody = (array)json_decode($rawBody, true);
        } elseif (str_contains($contentType, 'form-data')) {
            $boundary = '--' . strstr($contentType, '--') . "\r\n";
            $rawBody = substr($rawBody, 0, -strlen($boundary) - 2);
            foreach (explode($boundary, $rawBody) as $rawBoundary) {
                if (empty($rawBoundary)) continue;

                [$boundary_header, $boundary_value] = explode("\r\n\r\n", $rawBoundary, 2);
                $boundary_header = strtolower($boundary_header);
                $boundary_value = substr($boundary_value, 0, -2);

                preg_match('/name="(.*?)"/', $boundary_header, $name);
                if (empty($name)) continue;

                preg_match('/filename="(.*?)"/', $boundary_header, $filename);
                preg_match('/content-type: (.+)?/', $boundary_header, $type);

                // Is not a file
                if (empty($filename) || empty($type)) {
                    $parsedBody[$name[1]] = $boundary_value;
                    continue;
                }

                // Is a file
                $error = UPLOAD_ERR_OK;
                $tmp_name = tempnam(sys_get_temp_dir(), 'rush_upload_');
                if (false === $tmp_name || false === file_put_contents($tmp_name, $boundary_value)) {
                    $error = UPLOAD_ERR_CANT_WRITE;
                }

                $files[$name[1]] = [
                    'tmp_name' => $tmp_name,
                    'name' => $filename[1],
                    'size' => strlen($boundary_value),
                    'type' => $type[1],
                    'error' => $error
                ];
            }
        } else {
            parse_str($rawBody, $parsedBody);
        }

        return [$parsedBody, $files];
    }

    /**
     * Compile response data bag to raw.
     * @param string $version
     * @param int $code
     * @param string $phrase
     * @param array $headers
     * @param string $body
     * @return string
     */
    protected function compileResponseBag(
        string $version, int $code, string $phrase, array $headers, string $body
    ): string
    {
        // compile line
        $raw = sprintf("HTTP/%s %d %s\r\n", $version, $code, $phrase);

        // compile header
        $headers["content-length"] = [strlen($body)];

        $cookies = $headers["set-cookie"] ?? [];
        unset($headers["set-cookie"]);

        ksort($headers);
        foreach ($headers as $name => $header) {
            $raw .= sprintf("%s: %s\r\n", $name, implode(";", $header));
        }

        foreach ($cookies as $cookie) {
            $raw .= sprintf("set-cookie: %s\r\n", $cookie);
        }

        // compile body
        $raw .= sprintf("\r\n%s", $body);

        return $raw;
    }
}
