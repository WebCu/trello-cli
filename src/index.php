<?php

namespace Trello\CLI;

use Symfony\Component\Console\Application;
use Symfony\Component\Dotenv\Dotenv;
use Trello\CLI\Command\CreateLinkCardCommand;

require_once __DIR__.'/../vendor/autoload.php';

$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/../.env');

$application = new Application();

$application->add(new CreateLinkCardCommand());

$application->run();

//$httpClient = HttpClient::create();
//
//try {
//    $response = $httpClient->request(
//        'GET',
//        'https://www.thoughtworks.com/insights/articles/enduring-techniques-technology-radar'
//    );
//    $content = $response->getContent();
//    $crawler = new Crawler($content);
//    $divCrawler = $crawler->filter('main ul')->first()->closest('div');
//    $checkListNames = $divCrawler->filter('h3')->each(fn(Crawler $node) => $node->text());
//    $checkListLinks = $divCrawler->filter('ul')->each(
//        function (Crawler $node) {
//            return $node->filter('a')->each(fn(Crawler $aCrawler) => [
//                'text' => $aCrawler->text(),
//                'href' => $aCrawler->attr('href')
//            ]);
//        }
//    );
//
//    $checkList = array_combine($checkListNames, $checkListLinks);
//    var_dump($checkList);
//} catch (Exception $exception) {
//    var_dump($exception);
//}
//
//// Trello
//$headers = array(
//    'Accept' => 'application/json'
//);
//
//$key = $_ENV['TRELLO_API_KEY'];
//$token = $_ENV['TRELLO_API_TOKEN'];;
//
//$response = $httpClient->request(
//    'POST',
//    "https://api.trello.com/1/checklists/5ea365138e0ef40e719fba50/checkItems?key={$key}&token={$token}",
//    ['body' => ['name' => 'cuatro']]
//);
//
//$content = $response->getContent();
//
//var_dump($content);
