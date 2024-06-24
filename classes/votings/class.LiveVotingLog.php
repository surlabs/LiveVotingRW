<?php
declare(strict_types=1);
/**
 * This file is part of the LiveVoting Repository Object plugin for ILIAS.
 * This plugin allows to create real time votings within ILIAS.
 *
 * The LiveVoting Repository Object plugin for ILIAS is open-source and licensed under GPL-3.0.
 * For license details, visit https://www.gnu.org/licenses/gpl-3.0.en.html.
 *
 * To report bugs or participate in discussions, visit the Mantis system and filter by
 * the category "LiveVoting" at https://mantis.ilias.de.
 *
 * More information and source code are available at:
 * https://github.com/surlabs/LiveVoting
 *
 * If you need support, please contact the maintainer of this software at:
 * info@surlabs.es
 *
 */

/**
 * Class LiveVotingLog
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 */
class LiveVotingLog
{
    private static LiveVotingLog $instance;
    private string $path;
    private string $filename;
    private string $tag;
    private string $log_format = '';

    /**
     * Log level 10: Log only fatal errors that could lead to serious problems
     */
    private int $FATAL;

    /**
     * Log level 20: This is the standard log level that is set if no level is given
     */
    private int $WARNING;

    /**
     * Log level 30: Logs messages and notices that are less important for system functionality like not translated language values
     */
    private int $MESSAGE;

    /**
     * @var null|resource
     */
    private $fp = null;

    protected int $default_log_level;
    protected int $current_log_level;
    protected bool $enabled;

    /**
     * @throws ilLogException
     */
    public function __construct(
        string $a_log_path,
        string $a_log_file,
        string $a_tag = "",
        bool   $a_enabled = true,
        ?int   $a_log_level = null
    )
    {
        // init vars

        $this->FATAL = ilLogLevel::CRITICAL;
        $this->WARNING = ilLogLevel::WARNING;
        $this->MESSAGE = ilLogLevel::INFO;

        $this->default_log_level = $this->WARNING;
        $this->current_log_level = $this->setLogLevel($a_log_level ?? $this->default_log_level);

        $this->path = ($a_log_path) ?: ILIAS_ABSOLUTE_PATH;
        $this->filename = ($a_log_file) ?: "ilias.log";
        $this->tag = ($a_tag == "") ? "unknown" : $a_tag;
        $this->enabled = $a_enabled;
        $this->setLogFormat(date("[y-m-d H:i:s] ") . "[" . $this->tag . "] ");
        $this->open();
    }

    public static function getInstance(): LiveVotingLog
    {
        if (!isset(self::$instance)) {
            if (ILIAS_LOG_DIR === "php:/" && ILIAS_LOG_FILE === "stdout") {
                // Fix Docker-ILIAS log
                self::$instance = new self(ILIAS_LOG_DIR, ILIAS_LOG_FILE);
            } else {
                self::$instance = new self(ILIAS_LOG_DIR, "livevoting.log");
            }
        }

        return self::$instance;
    }

    public function setLogLevel(int $a_log_level): int
    {
        switch ($a_log_level) {
            case $this->FATAL:
                return $this->FATAL;
            case $this->WARNING:
                return $this->WARNING;
            case $this->MESSAGE:
                return $this->MESSAGE;
            default:
                return $this->default_log_level;
        }
    }

    /**
     * @param int|null $a_log_level
     * @return int
     */
    public function checkLogLevel(?int $a_log_level = null): int
    {
        if (!isset($a_log_level)) {
            return $this->default_log_level;
        }

        return $a_log_level;
    }

    public function setLogFormat(string $a_format): void
    {
        $this->log_format = $a_format;
    }

    public function getLogFormat(): string
    {
        return $this->log_format;
    }

    public function setPath(string $a_str): void
    {
        $this->path = $a_str;

        // on filename change reload close current file
        if ($this->fp) {
            fclose($this->fp);
            $this->fp = null;
        }
    }

    public function setFilename(string $a_str): void
    {
        $this->filename = $a_str;

        // on filename change reload close current file
        if ($this->fp) {
            fclose($this->fp);
            $this->fp = null;
        }
    }

    /**
     * this function is automatically called by class.ilErrorHandler in case of an error
     * To log manually please use $this::write
     * @throws ilLogException
     */
    public function logError($a_code, string $a_msg): void
    {
        $a_code = (int)$a_code;
        switch ($a_code) {
            case 3:
                return; // don't log messages

            case 2:
                $error_level = "warning";
                break;

            case 1:
                $error_level = "fatal";
                break;

            default:
                $error_level = "unknown";
                break;
        }
        $this->write("ERROR (" . $error_level . "): " . $a_msg);
    }

    /**
     * logging
     *
     * this method logs anything you want. It appends a line to the given logfile.
     * Datetime and client id is appended automatically
     * You may set the log level in each call. Leave blank to use default log level
     * specified in ilias.ini:
     * [log]
     * level = "<level>" possible values are fatal,warning,message
     *
     *
     * @param ?int $a_log_level
     *
     * @throws ilLogException
     */
    public function write(string $a_msg, int $a_log_level = null): void
    {
        if ($this->enabled and $this->current_log_level >= $this->checkLogLevel($a_log_level)) {
            $this->open();

            fwrite($this->fp, $this->getLogFormat() . $a_msg . "\n");

            if ($a_log_level == $this->FATAL) {
                $this->logStack();
            }
        }
    }

    /**
     * @throws ilLogException
     */
    public function logStack(string $a_message = ''): void
    {
        try {
            throw new Exception($a_message);
        } catch (Exception $e) {
            $this->write($e->getTraceAsString());
        }
    }

    /**
     * @throws ilLogException
     */
    public function dump($a_var, ?int $a_log_level = null): void
    {
        $this->write(print_r($a_var, true), $a_log_level);
    }

    /**
     * Open log file
     * @throws ilLogException
     */
    private function open(): void
    {
        if (!$this->fp) {
            $this->fp = @fopen($this->path . "/" . $this->filename, "a");
        }

        if (!$this->fp && $this->enabled) {
            throw new ilLogException('Unable to open log file for writing. Please check setup path to log file and possible write access.');
        }
    }

    /**
     * delete logfile
     */
    public function delete(): void
    {
        if (is_file($this->path . "/" . $this->filename)) {
            unlink($this->path . "/" . $this->filename);
        }
    }
}