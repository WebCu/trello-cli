<?php

namespace Trello\CLI\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class TrelloApi
{
    private HttpClientInterface $httpClient;
    private array $defaultQuery;

    public function __construct(HttpClientInterface $httpClient, string $key, string $token)
    {
        $this->httpClient = $httpClient;
        $this->defaultQuery = [
            "key" => $key,
            "token" => $token,
        ];
    }

    // Todo Select the active boards only (Active != Archived)
    public function getTrelloBoards(): array
    {
        $response = $this->httpClient->request(
            "GET",
            "https://api.trello.com/1/members/me/boards",
            ["query" => array_merge($this->defaultQuery, ['fields' => 'name'])]
        );

        return array_column($response->toArray(), null, "name");
    }

    public function getBoardLists(string $boardId): array
    {
        $response = $this->httpClient->request(
            "GET",
            "https://api.trello.com/1/boards/{$boardId}/lists",
            ["query" => array_merge($this->defaultQuery, ['fields' => 'name'])]
        );

        return array_column($response->toArray(), null, "name");
    }

    public function createCard(string $cardName, string $idList): array
    {
        $response = $this->httpClient->request(
            "POST",
            "https://api.trello.com/1/cards",
            [
                "body" => ["name" => $cardName],
                "query" => array_merge($this->defaultQuery, ["idList" => $idList])
            ]
        );

        return $response->toArray();
    }

    public function createChecklist(string $idCard): array
    {
        $response = $this->httpClient->request(
            "POST",
            "https://api.trello.com/1/checklists",
            ["query" => array_merge($this->defaultQuery, ["idCard" => $idCard])]
        );

        return $response->toArray();
    }

    public function createCheckItems(string $idChecklist, string $text, string $href): void
    {
        $this->httpClient->request(
            "POST",
            "https://api.trello.com/1/checklists/{$idChecklist}/checkItems",
            [
                "body" => ["name" => "[{$text}]({$href})"],
                "query" => array_merge($this->defaultQuery, ["fields" => "name"])
            ]
        );
    }
}
