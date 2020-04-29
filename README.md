# TRELLO CLI
A CLI to handle repetitive tasks in Trello.

## Installation
- After have cloned the repo. Go to the folder where the 
project was clone and start the php container:

    `docker-compose up -d`

- Go inside the container:

    `docker-compose exec php bash`
    
- Install the dependencies:

    `composer install`
    
## Configuration
Make a copy of .env.dist and named it .env. Add to the
.env file your Trello's API and Toke. You can find information
about how to get that info in this guide:
    
## Working with the CLI
All this commands must be executed inside of the 
container.
 
- Create a card with a checklist where the check items
are links extracted from a web page.

`php index.php trello-cli:create-link-card` 
