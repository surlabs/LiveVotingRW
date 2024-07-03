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

namespace LiveVoting\questions;

use LiveVoting\platform\LiveVotingDatabase;
use LiveVoting\platform\LiveVotingException;
use LiveVoting\questions\QuestionTypes\LiveVotingChoicesQuestion;
use LiveVoting\questions\QuestionTypes\LiveVotingFreeTextQuestion;
use LiveVoting\questions\QuestionTypes\LiveVotingNumberRangeQuestion;
use LiveVoting\questions\QuestionTypes\LiveVotingOrderQuestion;

/**
 * Abstract Class LiveVotingQuestion
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 */
abstract class LiveVotingQuestion
{
    const QUESTION_TYPES_IDS = array(
        "Choices" => 1,
        "FreeText" => 2,
        "CorrectOrder" => 4,
        "Priorities" => 5,
        "NumberRange" => 6,
    );

    protected int $id = 0;
    protected string $title = "";
    protected string $question = "";
    protected int $position = 99;
    protected int $voting_status = 5;
    protected array $options = array();

    public function __construct(?array $data = null) {
        if ($data !== null) {
            $this->id = (int) $data["id"];
            $this->title = (string) $data["title"];
            $this->question = (string) $data["question"];
            $this->position = (int) $data["position"];
            $this->voting_status = (int) $data["voting_status"];
            $this->options = $data["options"];
        }
    }

    public abstract function getQuestionType() : string;

    public function getQuestionTypeId(): int {
        return self::QUESTION_TYPES_IDS[$this->getQuestionType()];
    }

    /**
     * @throws LiveVotingException
     */
    public static function loadAllQuestionsByObjectId(int $obj_id): array
    {
        $questions = array();

        $database = new LiveVotingDatabase();

        $result = $database->select("rep_robj_xlvo_voting_n", array(
            "obj_id" => $obj_id
        ), null, "ORDER BY position ASC");

        if ($result) {
            foreach ($result as $row) {
                $row["options"] = LiveVotingQuestionOption::loadAllOptionsByVotingId((int) $row["id"]);

                switch ($row["voting_type"]) {
                    case self::QUESTION_TYPES_IDS["Choices"]:
                        $question = new LiveVotingChoicesQuestion($row);
                        break;
                    case self::QUESTION_TYPES_IDS["FreeText"]:
                        $question = new LiveVotingFreeTextQuestion($row);
                        break;
                    case self::QUESTION_TYPES_IDS["CorrectOrder"]:
                        $question = new LiveVotingOrderQuestion($row);
                        $question->setCorrectOrder(true);
                        break;
                    case self::QUESTION_TYPES_IDS["Priorities"]:
                        $question = new LiveVotingOrderQuestion($row);
                        break;
                    case self::QUESTION_TYPES_IDS["NumberRange"]:
                        $question = new LiveVotingNumberRangeQuestion($row);
                        break;
                }

                $questions[] = $question;
            }
        }

        return $questions;
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
            $result[0]["options"] = LiveVotingQuestionOption::loadAllOptionsByVotingId($id);

            switch ($result[0]["voting_type"]) {
                case self::QUESTION_TYPES_IDS["Choices"]:
                    $question = new LiveVotingChoicesQuestion($result[0]);
                    break;
                case self::QUESTION_TYPES_IDS["FreeText"]:
                    $question = new LiveVotingFreeTextQuestion($result[0]);
                    break;
                case self::QUESTION_TYPES_IDS["CorrectOrder"]:
                    $question = new LiveVotingOrderQuestion($result[0]);
                    $question->setCorrectOrder(true);
                    break;
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
            case "CorrectOrder":
                $question = new LiveVotingOrderQuestion();
                $question->setCorrectOrder(true);
                break;
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
    public function save(?int $obj_id = null) : int {
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

        foreach ($this->options as $option) {
            $option->save($this->id);
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

    public function getQuestion(): string
    {
        return $this->question;
    }

    public function setQuestion(string $question): void
    {
        $this->question = $question;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getVotingStatus(): int
    {
        return $this->voting_status;
    }

    public function setVotingStatus(int $voting_status): void
    {
        $this->voting_status = $voting_status;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    public function removeOption(int $option_id): void
    {
        foreach ($this->options as $key => $option) {
            if ($option->getId() == $option_id) {
                unset($this->options[$key]);
                break;
            }
        }
    }

    /**
     * @throws LiveVotingException
     */
    public function isFirst(): bool
    {
        $database = new LiveVotingDatabase();

        $result = $database->select("rep_robj_xlvo_voting_n", array(
            "obj_id" => $this->id
        ), ["id"], "ORDER BY position ASC");

        if (isset($result[0])) {
            return (int) $result[0]["id"] == $this->id;
        }

        return false;
    }

    /**
     * @throws LiveVotingException
     */
    public function isLast(): bool
    {
        $database = new LiveVotingDatabase();

        $result = $database->select("rep_robj_xlvo_voting_n", array(
            "obj_id" => $this->id
        ), ["id"], "ORDER BY position DESC");

        if (isset($result[0])) {
            return (int) $result[0]["id"] == $this->id;
        }

        return false;
    }

    abstract function getComputedColums(): float;
}