<?php

namespace Trello\CLI\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Trello\CLI\Service\TrelloApi;
use Trello\CLI\Service\WebCrawler;

class CreateLinkCardCommand extends Command
{
    protected static $defaultName = "trello-cli:create-link-card";

    /** @var TrelloApi  */
    private TrelloApi $trelloApi;

    /** @var WebCrawler  */
    private WebCrawler $webCrawler;

    public function __construct(TrelloApi $trelloApi, WebCrawler $webCrawler)
    {
        // best practices recommend to call the parent constructor first and
        // then set your own properties. That wouldn"t work in this case
        // because configure() needs the properties set in this constructor
        $this->trelloApi = $trelloApi;
        $this->webCrawler = $webCrawler;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription("Creates a new Trello card with a checklist")
            ->setHelp("
                This command allows create a new Trello card with a checklist filled
                with links extracted from a given web page.
            ");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper("question");

        ### Select board ###
        $trelloBoards = $this->trelloApi->getTrelloBoards();

        $question = new ChoiceQuestion(
            "Please select the board where you want to add the new card",
            array_keys($trelloBoards),
        );
        $question->setErrorMessage("Board %s is invalid.");

        $boardName = $helper->ask($input, $output, $question);
        $output->writeln("You have just selected: ".$boardName);
        ### End Select board ###

        ### Select List ###
        $boardLists = $this->trelloApi->getBoardLists($trelloBoards[$boardName]["id"]);

        $question = new ChoiceQuestion(
            "Please select the list where you want to add the new card",
            array_keys($boardLists),
        );
        $question->setErrorMessage("List %s is invalid.");

        $listName = $helper->ask($input, $output, $question);
        $output->writeln("You have just selected: ".$listName);
        ### End Select List ###

        ### Set the name of the new card ###
        $question = new Question("Please enter the name of the new card: ");
        $newCardName = $helper->ask($input, $output, $question);
        ### End Set the name of the new card ###

        ### Set the web page from where to extract the links ###
        $question = new Question("Please enter the url from where to extract the links: ");
        $url = $helper->ask($input, $output, $question);
        ### End Set the web page from where to extract the links ###

        ### Narrow dow the section from where to extract the links ###
        $question = new Question("Please enter a selector to narrow down the link extraction: ", "");
        $selector = $helper->ask($input, $output, $question);
        ### Narrow dow the section from where to extract the links ###

        $links = $this->webCrawler->extractLinksFrom($url, $selector);

        $newCard = $this->trelloApi->createCard($newCardName, $boardLists[$listName]["id"]);
        $newChecklist = $this->trelloApi->createChecklist($newCard["id"]);

        $this->createCheckItems($links, $newChecklist["id"], $output);

        $output->writeln("");
        $output->writeln("The card: {$newCardName} have been created");

        return 0;
    }

    private function createCheckItems(array $links, $idCheckList, OutputInterface $output)
    {
        $progressBar = new ProgressBar($output, count($links));
        $progressBar->start();

        foreach ($links as $link) {
            $this->trelloApi->createCheckItems($idCheckList, $link['text'], $link['href']);
            $progressBar->advance();
        }

        $progressBar->finish();
    }
}