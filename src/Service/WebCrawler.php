<?php

namespace Trello\CLI\Service;

use Exception;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WebCrawler
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function extractLinksFrom(string $url, string $selector): array
    {
        try {
            $response = $this->httpClient->request(
                "GET",
                $url
            );
            $content = $response->getContent();
            $crawler = new Crawler($content);

            return array_filter(
                $crawler->filter($selector . " a")->each(
                    fn(Crawler $aCrawler) => [
                        "text" => $aCrawler->text(),
                        "href" => $aCrawler->attr("href")
                    ]
                ),
                fn(array $link) => !empty($link["href"])
            );
        } catch (Exception $exception) {
            echo "An error happened: ".$exception->getMessage();

            return [];
        }
    }
}