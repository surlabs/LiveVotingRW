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

namespace LiveVoting\votings;

use LiveVoting\platform\LiveVotingDatabase;
use LiveVoting\platform\LiveVotingException;
use LiveVoting\Utils\ParamManager;

/**
 * Class LiveVotingPlayer
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 */
class LiveVotingPlayer
{
    const STAT_STOPPED = 0;
    const STAT_RUNNING = 1;
    const STAT_START_VOTING = 2;
    const STAT_END_VOTING = 3;
    const STAT_FROZEN = 4;

    private int $id;
    private int $obj_id;
    private int $active_voting;
    private int $status;
    private bool $frozen = true;
    private int $timestamp_refresh;
    private bool $show_results = false;
    private array $button_states = array();
    private int $countdown = 0;
    private int $countdown_start = 0;
    private bool $force_reload = false;
    private int $round_id = 0;

    /**
     * @throws LiveVotingException
     */
    public function __construct(?int $id = null)
    {
        if ($id !== null && $id != 0) {
            $this->setId($id);

            $this->loadFromDB();
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function setObjId(int $obj_id): void
    {
        $this->obj_id = $obj_id;
    }

    public function getActiveVoting(): int
    {
        return $this->active_voting;
    }

    public function setActiveVoting(int $active_voting): void
    {
        $this->active_voting = $active_voting;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    public function isFrozen(): bool
    {
        return $this->frozen;
    }

    public function setFrozen(bool $frozen): void
    {
        $this->frozen = $frozen;
    }

    public function getTimestampRefresh(): int
    {
        return $this->timestamp_refresh;
    }

    public function setTimestampRefresh(int $timestamp_refresh): void
    {
        $this->timestamp_refresh = $timestamp_refresh;
    }

    public function isShowResults(): bool
    {
        return $this->show_results;
    }

    public function setShowResults(bool $show_results): void
    {
        $this->show_results = $show_results;
    }

    public function getButtonStates(): array
    {
        return $this->button_states;
    }

    public function setButtonStates(array $button_states): void
    {
        $this->button_states = $button_states;
    }

    public function getCountdown(): int
    {
        return $this->countdown;
    }

    public function setCountdown(int $countdown): void
    {
        $this->countdown = $countdown;
    }

    public function getCountdownStart(): int
    {
        return $this->countdown_start;
    }

    public function setCountdownStart(int $countdown_start): void
    {
        $this->countdown_start = $countdown_start;
    }

    public function isForceReload(): bool
    {
        return $this->force_reload;
    }

    public function setForceReload(bool $force_reload): void
    {
        $this->force_reload = $force_reload;
    }

    public function getRoundId(): int
    {
        return $this->round_id;
    }

    public function setRoundId(int $round_id): void
    {
        $this->round_id = $round_id;
    }

    /**
     * @throws LiveVotingException
     */
    public function save(?int $obj_id = null): int {
        $database = new LiveVotingDatabase();

        if ($this->id != 0) {
            $database->update("rep_robj_xlvo_player_n", array(
                "active_voting" => $this->active_voting,
                "status" => $this->status,
                "frozen" => $this->frozen,
                "timestamp_refresh" => $this->timestamp_refresh,
                "show_results" => $this->show_results,
                "button_states" => json_encode($this->button_states),
                "countdown" => $this->countdown,
                "countdown_start" => $this->countdown_start,
                "force_reload" => $this->force_reload,
                "round_id" => $this->round_id
            ), array(
                "id" => $this->id
            ));
        } else if ($obj_id !== null && $obj_id != 0) {
            $this->id = $database->nextId("rep_robj_xlvo_player_n");

            $database->insert("rep_robj_xlvo_player_n", array(
                "id" => $this->id,
                "obj_id" => $obj_id,
                "active_voting" => $this->active_voting,
                "status" => $this->status,
                "frozen" => $this->frozen,
                "timestamp_refresh" => $this->timestamp_refresh,
                "show_results" => $this->show_results,
                "button_states" => json_encode($this->button_states),
                "countdown" => $this->countdown,
                "countdown_start" => $this->countdown_start,
                "force_reload" => $this->force_reload,
                "round_id" => $this->round_id
            ));
        } else {
            throw new LiveVotingException("Invalid object id");
        }

        return $this->id;
    }

    /**
     * @throws LiveVotingException
     */
    public function loadFromDB(): void
    {
        $database = new LiveVotingDatabase();

        $result = $database->select("rep_robj_xlvo_player_n", ["id" => $this->getId()]);

        if (isset($result[0])) {
            $this->setId((int) $result[0]["id"]);
            $this->setObjId((int) $result[0]["obj_id"]);
            $this->setActiveVoting((int) $result[0]["active_voting"]);
            $this->setStatus((int) $result[0]["status"]);
            $this->setFrozen((bool) $result[0]["frozen"]);
            $this->setTimestampRefresh((int) $result[0]["timestamp_refresh"]);
            $this->setShowResults((bool) $result[0]["show_results"]);
            $this->setButtonStates(json_decode($result[0]["button_states"], true));
            $this->setCountdown((int) $result[0]["countdown"]);
            $this->setCountdownStart((int) $result[0]["countdown_start"]);
            $this->setForceReload((bool) $result[0]["force_reload"]);
            $this->setRoundId((int) $result[0]["round_id"]);
        }
    }

    /**
     * @throws LiveVotingException
     */
    public function delete(): void
    {
        $database = new LiveVotingDatabase();

        $database->delete("rep_robj_xlvo_player_n", ["id" => $this->getId()]);
    }

    /**
     * @throws LiveVotingException
     */
    public function freeze(): void
    {
        $this->setFrozen(true);
        $this->resetCountDown(false);
        $this->setButtonStates([]);
        $this->resetCountDown(false);
        $this->setTimestampRefresh(time() + 30);
        $this->save();
    }


    /**
     * @param int $question_id
     * @throws LiveVotingException
     */
    public function unfreeze(int $question_id = 0): void
    {
        if ($question_id > 0) {
            $this->setActiveVoting($question_id);
        }

        $this->setFrozen(false);
        $this->resetCountDown(false);
        $this->setButtonStates([]);
        $this->resetCountDown(false);
        $this->setTimestampRefresh(time() + 30);
        $this->save();
    }


    /**
     * @param int $question_id
     * @throws LiveVotingException
     */
    public function toggleFreeze(int $question_id = 0): void
    {
        if ($this->isFrozen()) {
            $this->unfreeze($question_id);
        } else {
            $this->freeze();
        }
    }

    /**
     * @return int
     */
    public function remainingCountDown(): int
    {
        return $this->getCountdownStart() - time() + $this->getCountdown();
    }

    /**
     * @param int $seconds
     * @throws LiveVotingException
     */
    public function startCountDown(int $seconds): void
    {
        $param_manager = ParamManager::getInstance();
        $this->unfreeze($param_manager->getVoting());
        $this->setCountdown($seconds);
        $this->setCountdownStart(time());
        $this->save();
    }

    /**
     * @param bool $store
     * @throws LiveVotingException
     */
    public function resetCountDown(bool $store = true): void
    {
        $this->setCountdown(0);
        $this->setCountdownStart(0);

        if ($store) {
            $this->save();
        }
    }

    /**
     * @throws LiveVotingException
     */
    public function show(): void
    {
        $this->setShowResults(true);

        $this->save();
    }


    /**
     * @throws LiveVotingException
     */
    public function hide(): void
    {
        $this->setShowResults(false);

        $this->save();
    }


    /**
     * @throws LiveVotingException
     */
    public function toggleResults(): void
    {
        $this->setShowResults(!$this->isShowResults());

        $this->save();
    }


    /**
     * @throws LiveVotingException
     */
    public function terminate(): void
    {
        $this->setStatus(self::STAT_END_VOTING);
        $this->freeze();
    }

    /**
     * @return bool
     * @throws LiveVotingException
     */
    public function isFrozenOrUnattended(): bool
    {
        if ($this->getStatus() == self::STAT_RUNNING) {
            return ($this->isFrozen() || $this->isUnattended());
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function isCountDownRunning(): bool
    {
        return ($this->remainingCountDown() > 0 || $this->getCountdownStart() > 0);
    }


    /**
     * @return string
     */
    public function getCountdownClassname(): string
    {
        $cd = $this->remainingCountDown();

        return $cd > 10 ? 'running' : ($cd > 5 ? 'warning' : 'danger');
    }

    /**
     * @param $voting_id
     * @throws LiveVotingException
     */
    public function prepareStart($voting_id): void
    {
        $this->setStatus(self::STAT_START_VOTING);
        $this->setActiveVoting($voting_id);

        $this->setRoundId(LiveVotingRound::getLatestRoundId($this->getObjId()));

        $this->save();
    }

    /**
     * @throws LiveVotingException
     */
    public function isUnattended(): bool
    {
        if ($this->getStatus() != self::STAT_STOPPED AND ($this->getTimestampRefresh() < (time() - 30))) {
            $this->setStatus(self::STAT_STOPPED);

            $this->save();
        }
        if ($this->getStatus() == self::STAT_START_VOTING) {
            return false;
        }
        if ($this->getStatus() == self::STAT_STOPPED) {
            return false;
        }

        return ($this->getTimestampRefresh() < (time() - 4));
    }


    /**
     * @throws LiveVotingException
     */
    public function attend(): void
    {
        $this->setStatus(self::STAT_RUNNING);

        $this->setTimestampRefresh(time());

        if ($this->remainingCountDown() <= 0 && $this->getCountdownStart() > 0) {
            $this->freeze();
        }
    }
}