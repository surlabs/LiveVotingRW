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
 * Class LiveVotingNumberRangeQuestion
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 */
class LiveVotingNumberRangeQuestion extends LiveVotingQuestion
{
    public bool $percentage = true;
    public int $start_range = 0;
    public int $end_range = 100;
    public int $step_range = 1;
    public ?int $alt_result_display_mode = null;

    public function __construct(?array $data = null) {
        parent::__construct($data);

        if ($data !== null) {
            $this->percentage = (bool) $data["percentage"];
            $this->start_range = (int) $data["start_range"];
            $this->end_range = (int) $data["end_range"];
            $this->step_range = (int) $data["step_range"];
            $this->alt_result_display_mode = isset($data["alt_result_display_mode"]) ? (int) $data["alt_result_display_mode"] : null;
        }
    }

    public function getQuestionType(): string {
        return "NumberRange";
    }

    public function save(?int $obj_id): int {
        $id = parent::save($obj_id);

        $database = new LiveVotingDatabase();

        $database->update("rep_robj_xlvo_voting_n", array(
            "percentage" => (int) $this->percentage,
            "start_range" => $this->start_range,
            "end_range" => $this->end_range,
            "step_range" => $this->step_range,
            "alt_result_display_mode" => $this->alt_result_display_mode,
        ), array(
            "id" => $id
        ));

        return  $id;
    }
}