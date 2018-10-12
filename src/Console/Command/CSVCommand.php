<?php
/**
 * Created by PhpStorm.
 * User: joshgulledge
 * Date: 10/11/18
 * Time: 10:16 AM
 */

namespace LCI\MODX\LexiconHelper\Console\Command;


use LCI\MODX\Console\Command\BaseCommand;
use LCI\MODX\LexiconHelper\CSV;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CSVCommand extends BaseCommand
{
    public $loadMODX = false;

    protected function configure()
    {
        $primary_lang = 'en';
        if (!empty(getenv('LCI_MODX_LEXICON_HELPER_PRIMARY_LANGUAGE'))) {
            $primary_lang = getenv('LCI_MODX_LEXICON_HELPER_PRIMARY_LANGUAGE');
        }

        $default_export = __DIR__;
        if (!empty(getenv('LCI_MODX_LEXICON_HELPER_EXPORT_PATH'))) {
            $default_export = getenv('LCI_MODX_LEXICON_HELPER_EXPORT_PATH');
        }

        $default_import = __DIR__;
        if (!empty(getenv('LCI_MODX_LEXICON_HELPER_IMPORT_PATH'))) {
            $default_import = getenv('LCI_MODX_LEXICON_HELPER_IMPORT_PATH');
        }

        $default_lexicon_root = __DIR__;
        if (!empty(getenv('LCI_MODX_LEXICON_HELPER_LEXICON_ROOT_PATH'))) {
            $default_lexicon_root = getenv('LCI_MODX_LEXICON_HELPER_LEXICON_ROOT_PATH');
        }

        $this
            ->setName('lexicon-helper:csv')
            ->setDescription('Export/Import Lexicon data in CSV format')
            ->addArgument(
                'method',
                InputArgument::OPTIONAL,
                'Set as export or import',
                'export'
            )
            ->addOption(
                'lang',
                'l',
                InputOption::VALUE_OPTIONAL,
                'Primary/default language code, see: https://docs.modx.com/revolution/2.x/developing-in-modx/advanced-development/internationalization',
                $primary_lang
            )
            ->addOption(
                'export-path',
                'e',
                InputOption::VALUE_OPTIONAL,
                'The path to which the CSVs will be exported to',
                $default_export
            )
            ->addOption(
                'import-path',
                'i',
                InputOption::VALUE_OPTIONAL,
                'The path to which CSVs are located to be imported',
                $default_import
            )
            ->addOption(
                'skip',
                's',
                InputOption::VALUE_OPTIONAL,
                'Skip for first row of the CSV on import 1/0',
                true
            )
            ->addOption(
                'lexicon-root-path',
                'p',
                InputOption::VALUE_OPTIONAL,
                'The root lexicon path to which you would like to import/export. Example: /www/core/components/myextra/lexicon/ ',
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
     * @throws \LCI\MODX\LexiconHelper\Exception\CSVException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var SymfonyStyle $io */
        $io = new SymfonyStyle($input, $output);

        $method = strtolower(trim($input->getArgument('method')));

        $skip = $input->getOption('skip');

        /** @var CSV $lexiconCSV */
        $lexiconCSV = new CSV($this->consoleUserInteractionHandler);

        $lexiconCSV
            ->setPrimaryLanguage($input->getOption('lang'))
            ->setExportPath($input->getOption('export-path'))
            ->setImportPath($input->getOption('import-path'))
            ->setLexiconRootPath($input->getOption('lexicon-root-path'));

        if ($method === 'import') {
            $lexiconCSV->importCSVAndMakeLexiconLanguageFile($skip);

        } else {
            $lexiconCSV->exportLexiconLanguageAsCsv();

        }

        $output->writeln('Complete');
    }
}
