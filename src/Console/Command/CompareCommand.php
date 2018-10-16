<?php
/**
 * Created by PhpStorm.
 * User: joshgulledge
 * Date: 10/11/18
 * Time: 1:21 PM
 */

namespace LCI\MODX\LexiconHelper\Console\Command;

use LCI\MODX\Console\Command\BaseCommand;
use LCI\MODX\LexiconHelper\Compare;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CompareCommand extends BaseCommand
{
    public $loadMODX = false;

    protected function configure()
    {
        $primary_lang = 'en';
        if (!empty(getenv('LCI_MODX_LEXICON_HELPER_PRIMARY_LANGUAGE'))) {
            $primary_lang = getenv('LCI_MODX_LEXICON_HELPER_PRIMARY_LANGUAGE');
        }

        $default_lexicon_root = __DIR__;
        if (!empty(getenv('LCI_MODX_LEXICON_HELPER_LEXICON_ROOT_PATH'))) {
            $default_lexicon_root = getenv('LCI_MODX_LEXICON_HELPER_LEXICON_ROOT_PATH');
        }

        $this
            ->setName('lexicon-helper:compare')
            ->setDescription('Compare Lexicon key and value data')
            ->addArgument(
                'method',
                InputArgument::OPTIONAL,
                'Compare key only[key], duplicate values[duplicate] or values[values]',
                'key'
            )
            ->addOption(
                'lang',
                'l',
                InputOption::VALUE_OPTIONAL,
                'See: https://docs.modx.com/revolution/2.x/developing-in-modx/advanced-development/internationalization'
                . PHP_EOL . 'Primary/default language code',
                $primary_lang
            )
            ->addOption(
                'compare-lang',
                'c',
                InputOption::VALUE_REQUIRED,
                'The language to compare against the primary'
            )
            ->addOption(
                'topic',
                't',
                InputOption::VALUE_REQUIRED,
                'The topic to compare the languages against. Use 1 to compare all',
                'default'
            )
            ->addOption(
                'show',
                's',
                InputOption::VALUE_REQUIRED,
                'Show only key diffs. Use 0 to see all',
                true
            )
            ->addOption(
                'width',
                'w',
                InputOption::VALUE_REQUIRED,
                'Set max column width for lexicon values when the method argument is values',
                30
            )
            ->addOption(
                'lexicon-root-path',
                'p',
                InputOption::VALUE_OPTIONAL,
                'The root lexicon path to which you would like to import/export.' . PHP_EOL.'Example: /www/core/components/myextra/lexicon/ ',
                $default_lexicon_root
            );
    }

    /**
     * Runs the command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $method = strtolower(trim($input->getArgument('method')));

        /** @var Compare $lexiconCompare */
        $lexiconCompare = new Compare($this->consoleUserInteractionHandler);

        $lexiconCompare
            ->setPrimaryLanguage($input->getOption('lang'))
            ->setCompareLanguage($input->getOption('compare-lang'))
            ->setLexiconRootPath($input->getOption('lexicon-root-path'))
            ->setViewOnlyDiffs($input->getOption('show'))
            ->setLexiconValueMaxWidth($input->getOption('width'))
            ->setTopic($input->getOption('topic'));

        switch(strtolower(trim($method))) {
            case 'values':
                $lexiconCompare->compareValuesSideBySide($output);
                break;

            case 'duplicate':
                $lexiconCompare->findDuplicates($output);
                break;

            default:
                $lexiconCompare->diffKeysToConsoleTable($output);

        }

        $output->writeln('Complete');
    }
}