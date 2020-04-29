<?php

namespace Trello\CLI\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;

class CreateLinkCardCommand extends Command
{
    protected static $defaultName = 'trello-cli:create-link-card';

    protected function configure()
    {
        $this->setDescription('Creates a new Trello card with a checklist')
            ->setHelp('
                This command allows create a new Trello card with a checklist filled
                with links extracted from a given web page.
            ');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        $key = $_ENV['TRELLO_API_KEY'];
        $token = $_ENV['TRELLO_API_TOKEN'];

        $httpClient = HttpClient::create();

        # Select board

        // Todo Select the active boards only
        // Active != Archived
        $response = $httpClient->request(
            'GET',
            "https://api.trello.com/1/members/me/boards?fields=name&key={$key}&token={$token}",
        );

        $trelloBoards = array_column($response->toArray(), null, 'name');

        $question = new ChoiceQuestion(
            'Please select the board where you want to add the new card',
            array_keys($trelloBoards),
        );
        $question->setErrorMessage('Board %s is invalid.');

        $boardName = $helper->ask($input, $output, $question);
        $output->writeln('You have just selected: '.$boardName);

        # Select List
        $boardId = $trelloBoards[$boardName]['id'];
        $response = $httpClient->request(
            'GET',
            "https://api.trello.com/1/boards/{$boardId}/lists?fields=name&key={$key}&token={$token}",
        );

        $boardLists = array_column($response->toArray(), null, 'name');

        $question = new ChoiceQuestion(
            'Please select the list where you want to add the new card',
            array_keys($boardLists),
        );
        $question->setErrorMessage('List %s is invalid.');

        $listName = $helper->ask($input, $output, $question);
        $output->writeln('You have just selected: '.$listName);

        # Set the name of the new card
        $question = new Question('Please enter the name of the new card: ');
        $newCardName = $helper->ask($input, $output, $question);

        # Set the web page from where to extract the links
        $question = new Question('Please enter the url from where to extract the links: ');
        $url = $helper->ask($input, $output, $question);

        # Narrow dow the section from where to extract the links
        $question = new Question('Please enter a selector to narrow down the link extraction: ', '');
        $selector = $helper->ask($input, $output, $question);

        # Extract the links
        $httpClient = HttpClient::create();

        try {
            $response = $httpClient->request(
                'GET',
                $url
            );
            $content = $response->getContent();
            $crawler = new Crawler($content);
            $links = array_filter(
                $crawler->filter($selector . ' a')->each(
                    fn(Crawler $aCrawler) => [
                            'text' => $aCrawler->text(),
                            'href' => $aCrawler->attr('href')
                    ]
                ),
                fn(array $link) => !empty($link['href'])
            );
        } catch (Exception $exception) {
            echo 'error';
        }

        # Create a new card
        $idList = $boardLists[$listName]['id'];

        $response = $httpClient->request(
            "POST",
            "https://api.trello.com/1/cards?idList={$idList}&key={$key}&token={$token}",
            ["body" => ["name" => $newCardName]]
        );

        $newCard = $response->toArray();

        # Create a new Checklist
        $idCard = $newCard["id"];

        $response = $httpClient->request(
          "POST",
          "https://api.trello.com/1/checklists?idCard={$idCard}&key={$key}&token={$token}",
        );

        $newChecklist = $response->toArray();

        # Create the checkitems
        $idChecklist = $newChecklist['id'];

        // creates a new progress bar
        $progressBar = new ProgressBar($output, count($links));
        $progressBar->start();

        foreach ($links as $link) {
            $text = $link["text"];
            $href = $link["href"];
            $httpClient->request(
                "POST",
                "https://api.trello.com/1/checklists/{$idChecklist}/checkItems?key={$key}&token={$token}",
                ["body" => ["name" => "[{$text}]({$href})"]]
            );
            $progressBar->advance();
        }

        $progressBar->finish();

        $output->writeln('');
        $output->writeln("The new card: {$newCardName} have been created");

        return 0;
    }
}