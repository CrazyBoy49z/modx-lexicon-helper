<?php
/**
 * Created by PhpStorm.
 * User: joshgulledge
 * Date: 10/11/18
 * Time: 10:11 AM
 */

namespace LCI\MODX\LexiconHelper;

use FilesystemIterator;
use LCI\MODX\Console\Helpers\UserInteractionHandler;
use SplFileInfo;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

class Compare
{
    /** @var string */
    protected $lexicon_root_path = __DIR__.'/';

    /** @var string  */
    protected $primary_language = 'en';

    /** @var string  */
    protected $compare_language = '';

    /** @var string  */
    protected $topic = 'default';

    /** @var UserInteractionHandler */
    protected $userInteractionHandler;

    /** @var OutputInterface */
    protected $output;

    /** @var bool  */
    protected $view_only_diffs = true;

    /** @var int */
    protected $lexicon_value_max_width = 30;

    /**
     * CSV constructor.
     * @param UserInteractionHandler $userInteractionHandler
     */
    public function __construct(UserInteractionHandler $userInteractionHandler)
    {
        $this->userInteractionHandler = $userInteractionHandler;
    }

    /**
     * @param OutputInterface $output
     */
    public function diffKeysToConsoleTable(OutputInterface $output)
    {
        $this->output = $output;

        /** @var FilesystemIterator $it */
        $it = new FilesystemIterator($this->getLanguagePath($this->primary_language));

        /** @var SplFileInfo $fileInfo */
        foreach ($it as $fileInfo) {
            if (!$fileInfo->getExtension() == 'php') {
                continue;
            }
            //continue;
            $parts = explode('.', $fileInfo->getFilename());

            if ($this->topic != 1 && $this->topic != $parts[0]) {
                continue;
            }

            $this->userInteractionHandler->tellUser('Comparing topic '. $parts[0], UserInteractionHandler::MASSAGE_STRING);

            $this->outputConsoleKeyDiffTable(
                $this->output,
                $this->makeConsoleDiffKeyTableRows(
                    $this->getLexiconKeys($fileInfo->getRealPath()),
                    $this->getLexiconKeys($this->getLanguagePath($this->compare_language) . $fileInfo->getBasename())
                )
            );
        }
    }

    /**
     * @param OutputInterface $output
     */
    public function compareValuesSideBySide(OutputInterface $output)
    {
        $this->output = $output;

        /** @var FilesystemIterator $it */
        $it = new FilesystemIterator($this->getLanguagePath($this->primary_language));

        /** @var SplFileInfo $fileInfo */
        foreach ($it as $fileInfo) {
            if (!$fileInfo->getExtension() == 'php') {
                continue;
            }
            //continue;
            $parts = explode('.', $fileInfo->getFilename());

            if ($this->topic != 1 && $this->topic != $parts[0]) {
                continue;
            }

            $this->userInteractionHandler->tellUser('Comparing topic '. $parts[0], UserInteractionHandler::MASSAGE_STRING);

            $this->outputConsoleKeyValuesDiffTable(
                $this->output,
                $this->makeConsoleDiffKeyValuesTableRows(
                    $this->getLexicon($fileInfo->getRealPath()),
                    $this->getLexicon($this->getLanguagePath($this->compare_language) . $fileInfo->getBasename())
                )
            );
        }
    }

    /**
     * @param string $lexicon_root_path
     * @return Compare
     */
    public function setLexiconRootPath($lexicon_root_path)
    {
        $this->lexicon_root_path = $lexicon_root_path;
        return $this;
    }

    /**
     * @param string $primary_language
     * @return Compare
     */
    public function setPrimaryLanguage($primary_language)
    {
        $this->primary_language = $primary_language;
        return $this;
    }

    /**
     * @param string $compare_language
     * @return Compare
     */
    public function setCompareLanguage($compare_language)
    {
        $this->compare_language = $compare_language;
        return $this;
    }

    /**
     * @param string $topic
     * @return Compare
     */
    public function setTopic($topic)
    {
        $this->topic = $topic;
        return $this;
    }

    /**
     * @param bool $view_only_diffs
     * @return Compare
     */
    public function setViewOnlyDiffs(bool $view_only_diffs)
    {
        $this->view_only_diffs = $view_only_diffs;
        return $this;
    }

    /**
     * @param int $lexicon_value_max_width
     * @return $this
     */
    public function setLexiconValueMaxWidth($lexicon_value_max_width)
    {
        $this->lexicon_value_max_width = $lexicon_value_max_width;
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

    /**
     * @param string $file_path
     * @return array
     */
    protected function getLexicon($file_path)
    {
        $_lang = [];

        if (file_exists($file_path)) {
            $_lang = [];
            require $file_path;

            ksort($_lang);
        }

        return $_lang;
    }

    /**
     * @param array $primary
     * @param array $compare
     *
     * @return array
     */
    protected function makeConsoleDiffKeyValuesTableRows($primary, $compare)
    {
        $rows = $tmp_rows = $keys = [];

        foreach ($primary as $key => $value) {
            $keys[] = $key;
            if (isset($compare[$key])) {
                $tmp_rows[$key] = [
                    '',
                    '=',
                    $key,
                    $this->limitStringLength($value),
                    $this->limitStringLength($compare[$key])
                ];
            } else {
                $tmp_rows[$key] = [
                    '',
                    '-',
                    "<fg=red>{$key}</>",
                    $this->limitStringLength($value),
                    ''
                ];
            }
        }

        foreach ($compare as $key => $value) {
            if (in_array($key, $keys)) {
                continue;
            }

            $keys[] = $key;
            $tmp_rows[$key] = [
                '',
                '+',
                "<fg=red>{$key}</>",
                '',
                $this->limitStringLength($value)
            ];
        }

        sort($keys);
        $count = 1;
        foreach ($keys as $key) {
            if ($tmp_rows[$key][0] == '=' && $this->view_only_diffs) {
                continue;
            }

            $tmp_rows[$key][0] = $count++;

            $rows[] = $tmp_rows[$key];
        }

        return $rows;
    }

    /**
     * @param string $string
     * @return string
     */
    protected function limitStringLength($string)
    {
        $max_length = $this->lexicon_value_max_width;
        if ($length = strlen($string) <= $max_length) {
            return $string;
        }

        $tmp_string = substr($string, 0, $max_length);

        for ($break=$max_length; $break< $length; $break+= $max_length) {
            $stop = $max_length;

            if ($length - $break > $max_length) {
                $stop = $length - $break;
            }
            $tmp_string .= PHP_EOL . substr($string, $break - 1, $stop);
        }

        return $tmp_string;
    }

    /**
     * @param OutputInterface $output
     * @param array $rows
     */
    protected function outputConsoleKeyValuesDiffTable(OutputInterface $output, $rows=[])
    {
        $table = new Table($output);
        $table
            ->setHeaders(['Count', 'Diff', 'Key', $this->primary_language, $this->compare_language])
            ->addRows($rows);

        $table->render();
    }

    /**
     * @param string $file_path
     * @return array
     */
    protected function getLexiconKeys($file_path)
    {
        $keys = [];

        if (file_exists($file_path)) {
            $_lang = [];
            require $file_path;

            foreach ($_lang as $key => $value) {
                $keys[] = trim($key);
            }

            sort($keys);
        }

        return $keys;
    }

    /**
     * @param array $primary
     * @param array $compare
     *
     * @return array
     */
    protected function makeConsoleDiffKeyTableRows($primary, $compare)
    {
        $rows = $tmp_rows = $keys = [];

        foreach ($primary as $key) {
            $keys[] = $key;
            if (in_array($key, $compare)) {
                $tmp_rows[$key] = [
                    '',
                    '=',
                    $key,
                    $key
                ];
            } else {
                $tmp_rows[$key] = [
                    '',
                    '-',
                    "<fg=red>{$key}</>",
                    ''
                ];
            }
        }

        foreach ($compare as $key) {
            if (in_array($key, $keys)) {
                continue;
            }

            $keys[] = $key;
            $tmp_rows[$key] = [
                '',
                '+',
                '',
                "<fg=red>{$key}</>",
            ];
        }

        sort($keys);
        $count = 1;
        foreach ($keys as $key) {
            if ($tmp_rows[$key][1] == '=' && $this->view_only_diffs) {
                continue;
            }

            $tmp_rows[$key][0] = $count++;

            $rows[] = $tmp_rows[$key];
        }

        return $rows;
    }

    /**
     * @param OutputInterface $output
     * @param array $rows
     */
    protected function outputConsoleKeyDiffTable(OutputInterface $output, $rows=[])
    {
        $table = new Table($output);
        $table
            ->setHeaders(['Count', 'Diff', $this->primary_language, $this->compare_language])
            ->addRows($rows)
        ;
        $table->render();
    }



}