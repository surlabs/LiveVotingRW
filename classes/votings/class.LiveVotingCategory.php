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

/**
 * Class LiveVotingCategory
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 */
class LiveVotingCategory
{
    private int $id = 0;
    private string $title;
    private int $voting_id;
    private int $round_id;

    /**
     * @throws LiveVotingException
     */
    public function __construct(?int $id = null)
    {
        if ($id !== null && $id !== 0) {
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

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getVotingId(): int
    {
        return $this->voting_id;
    }

    public function setVotingId(int $voting_id): void
    {
        $this->voting_id = $voting_id;
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
    public function save(): int {
        $database = new LiveVotingDatabase();
        if ($this->id != 0) {
            $database->update("rep_robj_xlvo_cat", array(
                "title" => $this->title,
                "voting_id" => $this->voting_id,
                "round_id" => $this->round_id
            ), array(
                "id" => $this->id
            ));
        } else {
            $this->id = $database->nextId("rep_robj_xlvo_cat");

            $database->insert("rep_robj_xlvo_cat", array(
                "id" => $this->id,
                "title" => $this->title,
                "voting_id" => $this->voting_id,
                "round_id" => $this->round_id
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
            throw new LiveVotingException("Invalid category id");
        }

        $result = $database->select("rep_robj_xlvo_cat", ["id" => $this->getId()]);

        if (isset($result[0])) {
            if (isset($result[0]["title"])) {
                $this->setTitle($result[0]["title"]);
            }
            $this->setVotingId((int) $result[0]["voting_id"]);
            $this->setRoundId((int) $result[0]["round_id"]);
        } else {
            throw new LiveVotingException("Category not found");
        }
    }

    /**
     * @throws LiveVotingException
     */
    public function delete(): void
    {
        $database = new LiveVotingDatabase();

        $database->delete("rep_robj_xlvo_cat", ["id" => $this->getId()]);
    }
}