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

use ilLiveVotingPlugin;
use LiveVoting\platform\LiveVotingDatabase;
use LiveVoting\platform\LiveVotingException;

/**
 * Class LiveVotingPlayer
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 */
class LiveVotingRound
{
    private ?int $id = null;
    private int $obj_id;
    private int $round_number = 1;
    private ?string $title = null;

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

    public function getRoundNumber(): int
    {
        return $this->round_number;
    }

    public function setRoundNumber(int $round_number): void
    {
        $this->round_number = $round_number;
    }

    public function getTitle(): string
    {
        return $this->title ?? ilLiveVotingPlugin::getInstance()->txt("common_round") . " " . $this->round_number;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @throws LiveVotingException
     */
    public function save(): int {
        $database = new LiveVotingDatabase();

        if ($this->id != 0) {
            $database->update("rep_robj_xlvo_round_n", array(
                "obj_id" => $this->obj_id,
                "round_number" => $this->round_number,
                "title" => $this->title
            ), array(
                "id" => $this->id
            ));
        } else {
            $this->id = $database->nextId("rep_robj_xlvo_round_n");

            $database->insert("rep_robj_xlvo_round_n", array(
                "id" => $this->id,
                "obj_id" => $this->obj_id,
                "round_number" => $this->round_number,
                "title" => $this->title
            ));
        }

        return $this->id;
    }

    /**
     * @throws LiveVotingException
     */
    public function loadFromDB(): void
    {
        $database = new LiveVotingDatabase();

        if ($this->getId() == 0) {
            throw new LiveVotingException("Invalid round id");
        }

        $result = $database->select("rep_robj_xlvo_round_n", ["id" => $this->getId()]);

        if (isset($result[0])) {
            $this->setObjId((int) $result[0]["obj_id"]);
            $this->setRoundNumber((int) $result[0]["round_number"]);
            if (isset($result[0]["title"])) {
            	$this->setTitle($result[0]["title"]);
            }
        } else {
            throw new LiveVotingException("Round not found");
        }
    }

    /**
     * @throws LiveVotingException
     */
    public function delete(): void
    {
        $database = new LiveVotingDatabase();

        $database->delete("rep_robj_xlvo_round_n", ["id" => $this->getId()]);
    }

    /**
     * @param int $obj_id
     *
     * @return int
     * @throws LiveVotingException
     */
    public static function getLatestRoundId(int $obj_id): int
    {
        $database = new LiveVotingDatabase();

        $result = $database->select("rep_robj_xlvo_round_n", ["obj_id" => $obj_id], null, "ORDER BY id DESC LIMIT 1");

        if (isset($result[0])) {
            return (int) $result[0]["id"];
        } else {
            $round = self::createFirstRound($obj_id);

            return $round->getId();
        }
    }


    /**
     * Gets you the latest round for this object. creates the first one if there is no round yet.
     *
     * @param $obj_id int
     *
     * @return LiveVotingRound
     * @throws LiveVotingException
     */
    public static function getLatestRound(int $obj_id): LiveVotingRound
    {
        $round_id = self::getLatestRoundId($obj_id);

        return new self($round_id);
    }


    /**
     * @param $obj_id int
     *
     * @return LiveVotingRound
     * @throws LiveVotingException
     */
    public static function createFirstRound(int $obj_id): LiveVotingRound
    {
        $round = new self();

        $round->setRoundNumber(1);
        $round->setObjId($obj_id);

        $round->save($obj_id);

        return $round;
    }

    /**
     * @param $obj_id int
     *
     * @return LiveVotingRound[]
     * @throws LiveVotingException
     */
    public static function getRounds(int $obj_id): array
    {
        $database = new LiveVotingDatabase();

        $result = $database->select("rep_robj_xlvo_round_n", ["obj_id" => $obj_id], null, "ORDER BY id ASC");

        $rounds = array();

        foreach ($result as $row) {
            $round = new self();
            $round->setId((int) $row["id"]);
            $round->setObjId((int) $row["obj_id"]);
            $round->setRoundNumber((int) $row["round_number"]);
            if (isset($row["title"])) {
            	$round->setTitle($row["title"]);
            }

            $rounds[] = $round;
        }

        return $rounds;
    }
}