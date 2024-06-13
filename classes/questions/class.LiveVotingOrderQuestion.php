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

    public function hasCorrectSolution(): bool {
        //TODO Esta pregunta tiene los dos modos de corrección, por lo que se debe implementar la lógica de corrección
        return true;
    }

    public function getQuestionType(): string {
        return "Order";
    }

    public function save(?int $obj_id): int {
        $id = parent::save($obj_id);

        // TODO: Save specific data for this question type

        return  $id;
    }
}