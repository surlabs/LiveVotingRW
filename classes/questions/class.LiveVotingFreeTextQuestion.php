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
 * Class LiveVotingFreeTextQuestion
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 */
class LiveVotingFreeTextQuestion extends LiveVotingQuestion
{
    public bool $multi_free_input = false;
    public int $answer_field = 1;

    public function __construct(?array $data = null) {
        parent::__construct($data);

        if ($data !== null) {
            $this->multi_free_input = (bool) $data["multi_free_input"];
            $this->answer_field = (int) $data["answer_field"];
        }
    }

    public function getQuestionType(): string {
        return "FreeText";
    }

    public function save(?int $obj_id): int {
        $id = parent::save($obj_id);

        $database = new LiveVotingDatabase();

        $database->update("rep_robj_xlvo_voting_n", array(
            "multi_free_input" => (int) $this->multi_free_input,
            "answer_field" => $this->answer_field,
        ), array(
            "id" => $id
        ));

        return  $id;
    }
}