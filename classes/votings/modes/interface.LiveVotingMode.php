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
 * Interface LiveVotingMode
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 */
interface LiveVotingMode
{

    /**
     * If true, the voting has evaluable questions.
     * @return bool
     */
    public function hasEvaluation(): bool;

    /**
     * If true, the voting collects opinions.
     * @return bool
     */
    public function collectsOpinion(): bool;

    /**
     * If true, the user can vote multiple times in the same question.
     * @return bool
     */
    public function allowsMultipleVotes(): bool;

    /**
     * If true, the general results are shown to the users.
     * @return bool
     */
    public function showResults(): bool;

    /**
     * If true, the user can vote directly without waiting for the teacher to start the voting.
     * @return bool
     */
    public function directVoting(): bool;

    /**
     * If true, the user can change his vote while the voting is active.
     * @return bool
     */
    public function allowChangeVote(): bool;

}