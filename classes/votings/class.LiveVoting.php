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
 * Class LiveVoting
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 */
class LiveVoting
{
    /**
     * @var int The id of the voting
     */
    private int $id;

    /**
     * How the voting is being conducted
     * @var LiveVotingMode
     */
    private LiveVotingMode $mode;

    /**
     * The pin to access the voting for non-registered users
     * @var LiveVotingPin
     */
    private LiveVotingPin $pin;

    /**
     * The log of the voting
     * @var LiveVotingLog
     */
    private LiveVotingLog $log;

    /**
     * Which questions are being asked
     * @var LiveVotingQuestion[]
     */
    private array $questions;

    /**
     * Who is participating in the voting
     * @var LiveVotingParticipant[]
     */
    private array $participants;

    /**
     * @var bool Whether the voting is active or not
     */
    private bool $is_active;

    /**
     * LiveVoting constructor.
     * @param int $id
     */
    public function __construct(int $id)
    {
        $this->setId($id);
    }

    private function init(): bool
    {

    }

    public function getId(): int
    {
        return $this->id;
    }

    private function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getMode(): LiveVotingMode
    {
        return $this->mode;
    }

    public function setMode(LiveVotingMode $mode): void
    {
        $this->mode = $mode;
    }

    public function getPin(): LiveVotingPin
    {
        return $this->pin;
    }

    public function setPin(LiveVotingPin $pin): void
    {
        $this->pin = $pin;
    }

    public function getLog(): LiveVotingLog
    {
        return $this->log;
    }

    public function setLog(LiveVotingLog $log): void
    {
        $this->log = $log;
    }

    public function getQuestions(): array
    {
        return $this->questions;
    }

    public function setQuestions(array $questions): void
    {
        $this->questions = $questions;
    }

    public function getParticipants(): array
    {
        return $this->participants;
    }

    public function setParticipants(array $participants): void
    {
        $this->participants = $participants;
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function setActive(bool $is_active): void
    {
        $this->is_active = $is_active;
    }


}