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
    private string $pin;
    private bool $online;
    private bool $anonymous;
    private bool $voting_history;
    private bool $show_attendees;
    private int $frozen_behaviour;
    private int $results_behaviour;
    private string $puk;

    /**
     * LiveVoting constructor.
     * @param int $id
     */
    public function __construct(int $id)
    {
        $this->setId($id);
    }

    private function init(): void
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

    public function getPin(): string
    {
        return $this->pin;
    }

    public function setPin(string $pin): void
    {
        $this->pin = $pin;
    }

    public function isOnline(): bool
    {
        return $this->online;
    }

    public function setOnline(bool $online): void
    {
        $this->online = $online;
    }

    public function isAnonymous(): bool
    {
        return $this->anonymous;
    }

    public function setAnonymous(bool $anonymous): void
    {
        $this->anonymous = $anonymous;
    }

    public function isVotingHistory(): bool
    {
        return $this->voting_history;
    }

    public function setVotingHistory(bool $voting_history): void
    {
        $this->voting_history = $voting_history;
    }

    public function isShowAttendees(): bool
    {
        return $this->show_attendees;
    }

    public function setShowAttendees(bool $show_attendees): void
    {
        $this->show_attendees = $show_attendees;
    }

    public function getFrozenBehaviour(): int
    {
        return $this->frozen_behaviour;
    }

    public function setFrozenBehaviour(int $frozen_behaviour): void
    {
        $this->frozen_behaviour = $frozen_behaviour;
    }

    public function getresultsBehaviour(): int
    {
        return $this->results_behaviour;
    }

    public function setresultsBehaviour(int $results_behaviour): void
    {
        $this->results_behaviour = $results_behaviour;
    }

    public function getPuk(): string {
        return $this->puk;
    }

    public function setPuk(string $puk): void {
        $this->puk = $puk;
    }

    /**
     * @throws Exception
     */
    public function save(): int {
        if (!isset($this->id) || $this->id == 0) {
            throw new Exception("LiveVoting::save() - LiveVoting ID is 0");
        }

        $database = new LiveVotingDatabase();

        $database->insertOnDuplicatedKey("rep_robj_xlvo_config_n", array(
            "obj_id" => $this->id,
            "pin" => $this->pin,
            "obj_online" => (int) $this->online,
            "anonymous" => (int) $this->anonymous,
            "frozen_behaviour" => $this->frozen_behaviour,
            "results_behaviour" => $this->results_behaviour,
            "voting_history" => (int) $this->voting_history,
            "show_attendees" => (int) $this->show_attendees,
            "puk" => $this->puk
        ));

        return $this->id;
    }
}