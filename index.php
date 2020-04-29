<?php

namespace Trello\CLI;

use Symfony\Component\Console\Application;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;
use Trello\CLI\Command\CreateLinkCardCommand;
use Trello\CLI\Service\TrelloApi;
use Trello\CLI\Service\WebCrawler;

require_once __DIR__ . "/vendor/autoload.php";

$dotenv = new Dotenv();
$dotenv->load(__DIR__ . "/.env");

$httpClient = HttpClient::create();

$trelloApi = new TrelloApi($httpClient, $_ENV["TRELLO_API_KEY"], $_ENV["TRELLO_API_TOKEN"]);
$webCrawler = new WebCrawler($httpClient);

$application = new Application();
$application->add(new CreateLinkCardCommand($trelloApi, $webCrawler));
$application->run();