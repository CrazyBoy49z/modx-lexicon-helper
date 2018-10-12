<?php

namespace LCI\MODX\LexiconHelper;

use FilesystemIterator;
use LCI\MODX\Console\Helpers\UserInteractionHandler;
use LCI\MODX\LexiconHelper\Exception\CSVException;
use League\Csv\CannotInsertRecord;
use League\Csv\Reader;
use League\Csv\Writer;
use SplFileInfo;

/**
 * Class CSV
 * Will import/export CSV files
 */
class CSV
{
    /** @var string */
    protected $lexicon_root_path = __DIR__.'/';

    /** @var string  */
    protected $primary_language = 'en';

    /** @var string  */
    protected $export_path = __DIR__ . '/export/';

    /** @var string  */
    protected $import_path = __DIR__ . '/import/';

    /** @var UserInteractionHandler */
    protected $userInteractionHandler;

    /**
     * CSV constructor.
     * @param UserInteractionHandler $userInteractionHandler
     */
    public function __construct(UserInteractionHandler $userInteractionHandler)
    {
        $this->userInteractionHandler = $userInteractionHandler;
    }

    /**
     *
     */
    public function exportLexiconLanguageAsCsv()
    {
        /** @var FilesystemIterator $it */
        $it = new FilesystemIterator($this->getLanguagePath($this->primary_language));

        /** @var SplFileInfo $fileInfo */
        foreach ($it as $fileInfo) {
            if (!$fileInfo->getExtension() == 'php') {
                continue;
            }

            $this->userInteractionHandler->tellUser('Process file: '.$fileInfo->getRealPath(), UserInteractionHandler::MASSAGE_STRING);

            $_lang = [];
            require_once $fileInfo->getRealPath();

            $export_csv = $this->export_path . $this->primary_language . '.' . $fileInfo->getBasename('.php').'.csv';
            /** @var Writer $writer */
            $writer = Writer::createFromPath($export_csv, 'w+');

            try {
                $writer->insertOne(['Key', 'Content to translate']);

                foreach ($_lang as $key => $value) {
                    $writer->insertOne([$key, $value]);
                }

            } catch (CannotInsertRecord $e) {
                $this->userInteractionHandler->tellUser('Error: ' . $e->getMessage(), UserInteractionHandler::MASSAGE_STRING);

            } catch (\TypeError $e) {
                $this->userInteractionHandler->tellUser('Error: ' . $e->getMessage(), UserInteractionHandler::MASSAGE_STRING);

            }
        }
    }

    /**
     * @param bool $skip ~ skip the heading/first row of the CSV
     * @throws CSVException
     */
    public function importCSVAndMakeLexiconLanguageFile($skip=true)
    {
        /** @var FilesystemIterator $it */
        $it = new FilesystemIterator($this->import_path);

        /** @var SplFileInfo $fileInfo */
        foreach ($it as $fileInfo) {
            if (!$fileInfo->getExtension() == 'csv') {
                continue;
            }
            $parts = explode('.', $fileInfo->getFilename());

            if (count($parts) == 1) {
                throw new CSVException('Invalid CSV import file name: '. $fileInfo->getFilename().' '.PHP_EOL.
                    'Expected in format like en.default.csv');
            }

            $lang = $parts[0];

            $lexicon_code = '<?php'.PHP_EOL;

            $this->userInteractionHandler->tellUser('Import CSV: '.$fileInfo->getRealPath(), UserInteractionHandler::MASSAGE_STRING);

            $reader = Reader::createFromPath($fileInfo->getRealPath(), 'r');

            try {
                $records = $reader->getRecords();
                foreach ($records as $offset => $record) {
                    if ($skip && $offset == 0) {
                        continue;
                    }

                    $lexicon_code .= PHP_EOL .
                        '$_lang[\''.addslashes($record[0]).'\'] = \''.addslashes($record[1]).'\';';
                }

            } catch (\Exception $e) {
                $this->userInteractionHandler->tellUser('Error: ' . $e->getMessage(), UserInteractionHandler::MASSAGE_STRING);

            }

            $lang_path = $this->getLanguagePath($lang);

            if (!is_dir($lang_path)) {
                mkdir($lang_path);//, 0700, true);
            }

            $imported_file = $lang_path . DIRECTORY_SEPARATOR . $parts[1].'.inc.php';
            file_put_contents($imported_file, $lexicon_code);

            $this->userInteractionHandler->tellUser('New/Updated Lexicon file: ' . $imported_file, UserInteractionHandler::MASSAGE_STRING);
        }
    }


    /**
     * @param string $lexicon_root_path
     * @return CSV
     */
    public function setLexiconRootPath($lexicon_root_path)
    {
        $this->lexicon_root_path = $lexicon_root_path;
        return $this;
    }

    /**
     * @param string $primary_language
     * @return CSV
     */
    public function setPrimaryLanguage($primary_language)
    {
        $this->primary_language = $primary_language;
        return $this;
    }

    /**
     * @param string $export_path
     * @return CSV
     */
    public function setExportPath($export_path)
    {
        $this->export_path = $export_path;
        return $this;
    }

    /**
     * @param string $import_path
     * @return CSV
     */
    public function setImportPath($import_path)
    {
        $this->import_path = $import_path;
        return $this;
    }




    /**
     * @param string $lang
     * @return string
     */
    protected function getLanguagePath($lang)
    {
        return rtrim($this->lexicon_root_path, '/') . DIRECTORY_SEPARATOR . $lang . '/';
    }

}