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
 * Interface LiveVotingQuestion
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 */
abstract class LiveVotingQuestion
{
    const QUESTION_TYPES_IDS = array(
        "Choices" => 1,
        "FreeText" =>2,
        "Order" => 4,
        "Priorities" => 5, // This is the same as Order but its defined to give support to the old version of the plugin
        "NumberRange" => 6,
    );

    public int $id = 0;
    public string $title = "";
    public string $question = "";



    public function __construct(?int $id = null) {
        if ($id !== null) {
            $this->id = $id;
        }
    }

    public abstract function hasCorrectSolution() : bool;

    public abstract function getQuestionType() : string;

    public function getQuestionTypeId(): int {
        return self::QUESTION_TYPES_IDS[$this->getQuestionType()];
    }

    public static function loadQuestionById(int $id) : ?LiveVotingQuestion {
        // TODO: Get the question data from the database using $id

        $question = null;

        switch ($id) {
            case self::QUESTION_TYPES_IDS["Choices"]:
                $question = new LiveVotingChoicesQuestion($id);
                break;
            case self::QUESTION_TYPES_IDS["FreeText"]:
                $question = new LiveVotingFreeTextQuestion($id);
                break;
            case self::QUESTION_TYPES_IDS["Order"]:
            case self::QUESTION_TYPES_IDS["Priorities"]:
                $question = new LiveVotingOrderQuestion($id);
                break;
            case self::QUESTION_TYPES_IDS["NumberRange"]:
                $question = new LiveVotingNumberRangeQuestion($id);
                break;
        }

        return $question;
    }

    /**
     * @throws LiveVotingException
     */
    public static function loadNewQuestion(string $type) : ?LiveVotingQuestion {
        if (!array_key_exists($type, self::QUESTION_TYPES_IDS)) {
            throw new LiveVotingException("Invalid question type");
        }

        $question = null;

        switch ($type) {
            case "Choices":
                $question = new LiveVotingChoicesQuestion();
                break;
            case "FreeText":
                $question = new LiveVotingFreeTextQuestion();
                break;
            case "Order":
            case "Priorities":
                $question = new LiveVotingOrderQuestion();
                break;
            case "NumberRange":
                $question = new LiveVotingNumberRangeQuestion();
                break;
        }

        return $question;
    }

    public function delete() {
        // TODO: Delete the question from the database using $this->id
    }

    /**
     * @throws LiveVotingException
     */
    public function save(?int $obj_id) : int {
        if ($this->id != 0) {
            // TODO: Update the question in the database using $this->id
        } else if ($obj_id !== null && $obj_id != 0) {
            // TODO: Insert the question into the database
        } else {
            throw new LiveVotingException("Invalid object id");
        }

        return $this->id;
    }
}