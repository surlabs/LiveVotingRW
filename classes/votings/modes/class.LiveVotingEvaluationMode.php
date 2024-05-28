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
 * Class LiveVotingEvaluationMode
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 */
class LiveVotingEvaluationMode implements LiveVotingMode
{

    public function hasEvaluation() : bool
    {
        return true;
    }

    public function collectsOpinion() : bool
    {
        return false;
    }

    public function allowsMultipleVotes(): bool
    {
        return false;
    }

    public function showResults() : bool
    {
        //Dependiendo de la opción elegida por el profesor
        return true;
    }

    public function directVoting() : bool
    {
        //Dependiendo de la opción elegida por el profesor
        return false;
    }

    public function allowChangeVote() : bool
    {
        //Dependiendo de la opción elegida por el profesor
        return true;
    }
}