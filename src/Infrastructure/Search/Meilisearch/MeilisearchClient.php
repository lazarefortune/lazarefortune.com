<?php

namespace App\Infrastructure\Search\Meilisearch;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class MeilisearchClient
{
    public function __construct(private readonly string $host, private readonly string $apiKey, private readonly HttpClientInterface $client)
    {
    }

    public function get(string $endpoint): array
    {
        return $this->api($endpoint, [], 'GET');
    }

    public function post(string $endpoint, array $data = []): array
    {
        return $this->api($endpoint, $data, 'POST');
    }

    public function put(string $endpoint, array $data = []): array
    {
        return $this->api($endpoint, $data, 'PUT');
    }

    public function patch(string $endpoint, array $data = []): array
    {
        return $this->api($endpoint, $data, 'PATCH');
    }

    public function delete(string $endpoint, array $data = []): array
    {
        return $this->api($endpoint, $data, 'DELETE');
    }

    private function api(string $endpoint, array $data = [], string $method = 'POST'): array
    {
        $headers = [];
        if (!empty($this->apiKey)) {
            $headers['Authorization'] = 'Bearer ' . $this->apiKey;
        }

        // Vérifier si le host contient déjà un schéma (http:// ou https://)
        $host = $this->host;
        if (!preg_match('#^https?://#i', $host)) {
            $host = 'http://' . $host;
        }

        $url = rtrim($host, '/') . '/' . ltrim($endpoint, '/');
        $response = $this->client->request($method, $url, [
            'json' => $data,
            'headers' => $headers,
        ]);

        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            return json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        }
        throw new MeilisearchException($response);
    }

}