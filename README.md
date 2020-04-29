The first thing was create the tag to easily navigate
between the different stages of the POC.

https://www.jetbrains.com/help/phpstorm/2020.1/use-tags-to-mark-specific-commits.html

These are the default values of the Symfony Console
environment variables 
#### Symfony Console
APP_ENV=dev
APP_DEBUG=1

Add namespace and author to composer.json
"authors": [
        {"name": "Jorge Gonzalez (Webcu)"}
    ],

"autoload": {
        "psr-4": {
            "Trello\\CLI\\": "src/"
        }
    }
    
    
https://symfony.com/doc/current/components/console/helpers/questionhelper.html#let-the-user-choose-from-a-list-of-answers

Add the composer.lock to the repo to avoid problems in
the future because of new versions of the library that
we use.

Deciding between single quotes or double quotes:
Like I used a lot of variable interpolation I prefer
use double quotes

Creation of services 
Pass them to the command like arguments to be easily
Interchangeable.

HttpClient can have a wrapper? 
Yes, query string parameters 