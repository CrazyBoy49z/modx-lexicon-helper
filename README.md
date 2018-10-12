# MODX Lexicon Helper

Command line tool

Read on how MODX does [Internalization](https://docs.modx.com/revolution/2.x/developing-in-modx/advanced-development/internationalization)

## Features

- Export a Lexicon file to CSV format to allow your translator to work in Excel or related
- Import a CSV file and convert it to a valid MODX Lexicon file
- Compare Lexicon keys for languages to easily see missing or incorrect keys
- Compare Lexicon keys and values of a languages

## Quick Install

You can do a quick install and run this outside of MODX and only requires PHP to help you out.

- Create composer.json in your preferred directory, see below
- Now run ```composer install```
- Setup the .env file as mentioned below
- Run commands as: ```php src/bootstrap.php lexicon-helper:compare```

## Production install

Requires an installation of MODX

- Install [Orchestrator](https://github.com/LippertComponents/Orchestrator)
- Add to the composer.json file as below
- Add to your .env file parameters in the sample.env file and customize.
- Run ```composer update```
- Run commands through Orchestrator

### .env 

Copy the .sample.env and make .env and fill in values to match your environment.


### Composer.json

```json
{
  "require": {
       "lci/modx-lexicon-helper": "dev-master"
   },
  "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/LippertComponents/modx-lexicon-helper"
        }
    ],
  "minimum-stability": "dev"
}
```