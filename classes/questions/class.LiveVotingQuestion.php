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
    public int $position = 99;
    public int $voting_status = 5;


    public function __construct(?array $data = null) {
        if ($data !== null) {
            $this->id = (int) $data["id"];
            $this->title = (string) $data["title"];
            $this->question = (string) $data["question"];
            $this->position = (int) $data["position"];
            $this->voting_status = (int) $data["voting_status"];
        }
    }

    public abstract function getQuestionType() : string;

    public function getQuestionTypeId(): int {
        return self::QUESTION_TYPES_IDS[$this->getQuestionType()];
    }

    /**
     * @throws LiveVotingException
     */
    public static function loadQuestionById(int $id) : ?LiveVotingQuestion {
        $question = null;

        $database = new LiveVotingDatabase();

        $result = $database->select("rep_robj_xlvo_voting_n", array(
            "id" => $id
        ));

        if ($result && isset($result[0])) {
            switch ($result[0]["voting_type"]) {
                case self::QUESTION_TYPES_IDS["Choices"]:
                    $question = new LiveVotingChoicesQuestion($result[0]);
                    break;
                case self::QUESTION_TYPES_IDS["FreeText"]:
                    $question = new LiveVotingFreeTextQuestion($result[0]);
                    break;
                case self::QUESTION_TYPES_IDS["Order"]:
                case self::QUESTION_TYPES_IDS["Priorities"]:
                    $question = new LiveVotingOrderQuestion($result[0]);
                    break;
                case self::QUESTION_TYPES_IDS["NumberRange"]:
                    $question = new LiveVotingNumberRangeQuestion($result[0]);
                    break;
            }
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

    /**
     * @throws LiveVotingException
     */
    public function delete(): void
    {
        $database = new LiveVotingDatabase();

        $database->delete("rep_robj_xlvo_voting_n", array(
            "id" => $this->id
        ));
        $database->delete("rep_robj_xlvo_option_n", array(
            "voting_id" => $this->id
        ));
        $database->delete("rep_robj_xlvo_vote_n", array(
            "voting_id" => $this->id
        ));
    }

    /**
     * In this method we only save common data for all question types, specific data is saved in the child classes
     *
     * @throws LiveVotingException
     */
    public function save(?int $obj_id) : int {
        $database = new LiveVotingDatabase();

        if ($this->id != 0) {
            $database->update("rep_robj_xlvo_voting_n", array(
                "title" => $this->title,
                "question" => $this->question,
                "voting_status" => $this->voting_status,
                "position" => $this->position
            ), array(
                "id" => $this->id
            ));
        } else if ($obj_id !== null && $obj_id != 0) {
            $this->id = $database->nextId("rep_robj_xlvo_voting_n");

            $database->insert("rep_robj_xlvo_voting_n", array(
                "id" => $this->id,
                "obj_id" => $obj_id,
                "title" => $this->title,
                "question" => $this->question,
                "voting_type" => $this->getQuestionTypeId(),
                "voting_status" => $this->voting_status,
                "position" => $this->position
            ));
        } else {
            throw new LiveVotingException("Invalid object id");
        }

        return $this->id;
    }

    /**
     * @throws LiveVotingException
     */
    public function reset(): void
    {
        $database = new LiveVotingDatabase();

        $database->delete("rep_robj_xlvo_vote_n", array(
            "voting_id" => $this->id
        ));
    }
}