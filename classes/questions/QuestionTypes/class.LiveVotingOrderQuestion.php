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
 * Class LiveVotingOrderQuestion
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 */
class LiveVotingOrderQuestion extends LiveVotingQuestion
{
    private int $columns = 1;
    private bool $randomise_option_sequence = false;
    private bool $correct_order = false;

    public function __construct(?array $data = null) {
        parent::__construct($data);

        if ($data !== null) {
            $this->columns = (int) $data["columns"];
            $this->randomise_option_sequence = (bool) $data["randomise_option_sequence"];
        }
    }

    public function getQuestionType(): string {
        if ($this->correct_order) {
            return "CorrectOrder";
        } else {
            return "Priorities";
        }
    }

    public function save(?int $obj_id): int {
        $id = parent::save($obj_id);

        $database = new LiveVotingDatabase();

        $data = array(
            "columns" => $this->columns
        );

        if ($this->correct_order) {
            $data["randomise_option_sequence"] = (int) $this->randomise_option_sequence;
        }

        $database->update("rep_robj_xlvo_voting_n", $data, array(
            "id" => $id
        ));

        return  $id;
    }

    public function getColumns(): int
    {
        return $this->columns;
    }

    public function setColumns(int $columns): void
    {
        $this->columns = $columns;
    }

    public function isRandomiseOptionSequence(): bool
    {
        return $this->randomise_option_sequence;
    }

    public function setRandomiseOptionSequence(bool $randomise_option_sequence): void
    {
        $this->randomise_option_sequence = $randomise_option_sequence;
    }

    public function setCorrectOrder(bool $correct_order): void
    {
        $this->correct_order = $correct_order;
    }
}