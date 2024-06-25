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

use LiveVoting\Voting\LiveVotingParticipant;

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
    private bool $online = false;
    private bool $anonymous;
    private bool $voting_history;
    private bool $show_attendees;
    private int $frozen_behaviour;
    private int $results_behaviour;
    private string $puk;

    /**
     * LiveVoting constructor.
     * @param int $id
     * @param bool|null $loadFromDB
     * @throws LiveVotingException
     */
    public function __construct(int $id, ?bool $loadFromDB = false)
    {
        $this->setId($id);

        if ($loadFromDB) {
            $this->loadFromDB();
        }
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

    public function getResultsBehaviour(): int
    {
        return $this->results_behaviour;
    }

    public function setResultsBehaviour(int $results_behaviour): void
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
     * @throws LiveVotingException
     */
    public function save(): int {
        if (!isset($this->id) || $this->id == 0) {
            throw new LiveVotingException("LiveVoting::save() - LiveVoting ID is 0");
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

    /**
     * @throws LiveVotingException
     */
    public function loadFromDB(): void
    {
        $database = new LiveVotingDatabase();

        $result = $database->select("rep_robj_xlvo_config_n", ["obj_id" => $this->getId()]);

        if (isset($result[0])) {
            $this->setPin($result[0]["pin"]);
            $this->setOnline((bool) $result[0]["obj_online"]);
            $this->setAnonymous((bool) $result[0]["anonymous"]);
            $this->setFrozenBehaviour((int) $result[0]["frozen_behaviour"]);
            $this->setResultsBehaviour((int) $result[0]["results_behaviour"]);
            $this->setVotingHistory((bool) $result[0]["voting_history"]);
            $this->setShowAttendees((bool) $result[0]["show_attendees"]);
            $this->setPuk($result[0]["puk"]);
        } else {
            $this->loadDefaultValues();
        }

        $questions_id = $database->select("rep_robj_xlvo_voting_n", array(
            "obj_id" => $this->getId(),
        ), ["id"]);

        foreach ($questions_id as $question_id) {
            $this->questions[] = LiveVotingQuestion::loadQuestionById((int) $question_id["id"]);
        }
    }

    /**
     * Load default values for the voting
     * @throws LiveVotingException
     */
    private function loadDefaultValues(): void
    {
        $this->setPin($this->generatePin());
        $this->setOnline(false);
        $this->setAnonymous(false);
        $this->setFrozenBehaviour(1);
        $this->setResultsBehaviour(1);
        $this->setVotingHistory(false);
        $this->setShowAttendees(false);
        $this->setPuk($this->generatePuk());
    }

    /**
     * Generate a random pin
     * @throws LiveVotingException
     */
    public static function generatePin(): string
    {
        $database = new LiveVotingDatabase();
        $pin = LiveVoting::generateCode(4);

        $result = $database->select("rep_robj_xlvo_config_n", ["pin" => $pin]);

        while (isset($result[0])) {
            $pin = LiveVoting::generateCode(4);
            $result = $database->select("rep_robj_xlvo_config_n", ["pin" => $pin]);
        }

        return $pin;
    }

    /**
     * Generate a random puk
     * @throws LiveVotingException
     */
    public static function generatePuk(): string
    {
        $database = new LiveVotingDatabase();
        $puk = LiveVoting::generateCode(10);

        $result = $database->select("rep_robj_xlvo_config_n", ["puk" => $puk]);

        while (isset($result[0])) {
            $puk = LiveVoting::generateCode(10);
            $result = $database->select("rep_robj_xlvo_config_n", ["puk" => $puk]);
        }

        return $puk;
    }

    /**
     * Generate a random code
     *
     * @param int $lenght
     * @return string
     */
    private static function generateCode(int $lenght): string
    {
        $characters = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $pin = "";

        for ($i = 0; $i < $lenght; $i++) {
            $pin .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $pin;
    }

    /**
     * @return void
     * @throws LiveVotingException
     */
    public function delete(): void
    {
        $database = new LiveVotingDatabase();

        $database->delete("rep_robj_xlvo_config_n", ["obj_id" => $this->getId()]);

        foreach ($this->getQuestions() as $question) {
            $question->delete();
        }
    }

    public function getQuestionById(int $id): ?LiveVotingQuestion
    {
        foreach ($this->questions as $question) {
            if ($question->getId() === $id) {
                return $question;
            }
        }

        return null;
    }
}