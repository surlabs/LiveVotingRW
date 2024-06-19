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

namespace LiveVoting\UI;

use Exception;
use ilCtrlInterface;
use ilException;
use ilHtmlPurifierFactory;
use ilHtmlPurifierNotFoundException;
use ILIAS\UI\Component\Input\Container\Form\Form;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ilLiveVotingPlugin;
use ilObject;
use ilObjLiveVotingGUI;
use ilPlugin;
use ilPropertyFormGUI;
use ilSystemStyleException;
use ilTemplate;
use ilTemplateException;
use ilTextAreaInputGUI;
use LiveVotingException;
use LiveVotingQuestion;
use LiveVotingQuestionOption;

/**
 * Class LiveVotingChoicesUI
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 * @ilCtrl_IsCalledBy  ilObjLiveVotingGUI: ilObjPluginGUI
 */
class LiveVotingChoicesUI
{
    /**
     * @var LiveVotingQuestion
     */
    private LiveVotingQuestion $question;
    /**
     * @var ilCtrlInterface
     */
    protected ilCtrlInterface $control;
    /**
     * @var Factory
     */
    protected Factory $factory;

    /**
     * @var ilPlugin
     */
    protected ilPlugin $plugin;
    protected renderer $renderer;
    protected $request;

    public function __construct(?LiveVotingQuestion $question = null)
    {
        global $DIC;

        $this->plugin = ilLiveVotingPlugin::getInstance();
        $this->control = $DIC->ctrl();
        $this->request = $DIC->http()->request();
        $this->factory = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();

        if($question) {
            $this->question = $question;
        }
    }

    public function getChoicesForm(): Form
    {
        global $DIC;

        try {
            $form_questions = [];

            $field_title = $this->factory->input()->field()->text(
                $this->plugin->txt('voting_title'))
                ->withValue(isset($this->question) ? $this->question->getTitle() : "")
                ->withRequired(true);
/*                ->withAdditionalTransformation($DIC->refinery()->custom()->transformation(
                    function ($v) use ($object) {

                    }
                ));*/


            $form_questions["title"] = $field_title;


            $field_question = $this->factory->input()->field()->textarea(
                $this->plugin->txt('voting_question'))
                ->withValue(isset($this->question) ? $this->question->getQuestion() : "")
                ->withRequired(true);
            /*                ->withAdditionalTransformation($DIC->refinery()->custom()->transformation(
                                function ($v) use ($object) {

                                }
                            ));*/

            $form_questions["question"] = $field_question;

            $field_columns = $this->factory->input()->field()->select(
                $this->plugin->txt('voting_columns'),
                [1 => "1", 2 => "2", 3 => "3", 4 => "4", 5 => "5", 6 => "6", 7 => "7", 8 => "8", 9 => "9", 10 => "10"])
                ->withValue(1);

            $form_questions["columns"] = $field_columns;


            $section_questions = $this->factory->input()->field()->section($form_questions, $this->plugin->txt("player_voting_list"), $this->plugin->txt("voting_type_1"));


            //Answers section
            $form_answers = [];

            $field_selection = $this->factory->input()->field()->checkbox(
                $this->plugin->txt('qtype_1_multi_selection'),
                $this->plugin->txt('qtype_1_multi_selection_info'));

            $form_answers["selection"] = $field_selection;

            if(isset($this->question)) {
                $options = $this->question->getOptions();

            }

            $field_hidden = $this->factory->input()->field()->hidden()
                ->withValue(isset($options) ? htmlspecialchars(json_encode(array_map(function($option) {
                    return json_encode([
                        "text" => $option->getText(),
                        "id" => $option->getId()
                    ]);
                }, $options), JSON_UNESCAPED_UNICODE) ) : "")
                ->withOnLoadCode(function ($id) {
                    return "xlvo.initHiddenInput('".$id."')";
                })
                ->withLabel('options');

            $form_answers["hidden"] = $field_hidden;

            $field_input = $this->factory->input()->field()->text(
                $this->plugin->txt('qtype_1_options'))
                ->withOnLoadCode(function ($id) {
                    return "xlvo.initMultipleInputs('".$id."')";
                })
                ->withMaxLength(255)
                ->withRequired(true);


            $form_answers["input"] = $field_input;





            $section_answers = $this->factory->input()->field()->section($form_answers, $this->plugin->txt("qtype_form_header"), "");

           $sections =  [
                "config_question" => $section_questions,
                "config_answers" => $section_answers
            ];

            if(isset($options)){
                $this->control->setParameterByClass(ilObjLiveVotingGUI::class, "question_id", $this->question->getId());
                $form_action = $this->control->getFormActionByClass(ilObjLiveVotingGUI::class, "edit");

            } else {
                $form_action = $this->control->getFormActionByClass(ilObjLiveVotingGUI::class, "selectedChoices");
            }

            $DIC->ui()->mainTemplate()->addJavaScript($this->plugin->getDirectory() . "/templates/js/xlvo.js");

            $DIC->ui()->mainTemplate()->addCss($this->plugin->getDirectory() . "/templates/css/livevoting.css");


            return $this->createForm($form_action, $sections);


        } catch (Exception $e) {
            throw new ilException($e->getMessage());
        }
    }


    /**
     * @throws ilHtmlPurifierNotFoundException
     * @throws LiveVotingException
     * @throws \ilCtrlException
     */
    private function createForm(string $form_action, array $sections): Form
    {
        $r = new ilTextAreaInputGUI($this->plugin->txt('question'), 'question');
        $r->addPlugin('latex');
        $r->addButton('latex');
        $r->addButton('pastelatex');
        $r->setRequired(true);
        $r->setRTESupport(ilObject::_lookupObjId((int)$_GET['ref_id']), "dcl", ilLiveVotingPlugin::PLUGIN_ID, null, false);
        $r->setUseRte(true);
        $r->setRteTags(array(
            'p',
            'a',
            'br',
            'strong',
            'b',
            'i',
            'em',
            'span',
            'img',
        ));
        $r->usePurifier(false);
        $r->setPurifier(ilHtmlPurifierFactory::getInstanceByType('frm_post'));
        $r->disableButtons(array(
            'charmap',
            'undo',
            'redo',
            'justifyleft',
            'justifycenter',
            'justifyright',
            'justifyfull',
            'anchor',
            'fullscreen',
            'cut',
            'copy',
            'paste',
            'pastetext',
            'formatselect',
            'bullist',
            'hr',
            'sub',
            'sup',
            'numlist',
            'cite',
        ));

        $r->setRows(5);

        $form = new ilPropertyFormGUI();
        $form->addItem($r);

        return $this->factory->input()->container()->form()->standard(
            $form_action,
            $sections,
        );
    }

    /**
     * @throws LiveVotingException
     */
    public function save($result, ?int $question_id = null): int
    {


        if ($result && isset($result["config_question"], $result["config_answers"]["hidden"]) && $result["config_answers"]["hidden"] !== "") {
            $question_data = $result["config_question"];
            $options_data = json_decode($result["config_answers"]["hidden"]);


            if (!empty($options_data)) {
                $question = $question_id ? LiveVotingQuestion::loadQuestionById($question_id) : LiveVotingQuestion::loadNewQuestion("Choices");

                $question->setTitle($question_data["title"] ?? null);
                $question->setQuestion($question_data["question"] ?? null);
                $question->setColumns((int)($question_data["columns"] ?? 0));

                $old_options = $question->getOptions();

                foreach ($old_options as $old_option) {
                    $found = false;

                    foreach ($options_data as $index => $option_data) {
                        if ($option_data) {
                            if (is_string($option_data)) {
                                $option_data = json_decode($option_data);
                            }

                            if (isset($option_data->id) && $option_data->id == $old_option->getId()) {
                                $old_option->setPosition($index);

                                if (isset($option_data->text)) {
                                    $old_option->setText($option_data->text);
                                }

                                $old_option->save($question->getId());

                                $found = true;

                                $options_data[$index] = false;

                                break;
                            }
                        }
                    }

                    if (!$found) {
                        $old_option->delete();
                    }
                }

                foreach ($options_data as $index => $option_data) {
                    if ($option_data) {
                        if (is_string($option_data)) {
                            $option_data = json_decode($option_data);
                        }

                        $option = LiveVotingQuestionOption::loadNewOption($question->getQuestionTypeId());

                        if (isset($option_data->text)) {
                            $option->setText($option_data->text);
                        }

                        $option->setPosition($index);

                        $old_options[] = $option;
                    }
                }

                $question->setOptions($old_options);


                $id = ilObject::_lookupObjId((int)$_GET['ref_id']);

                return $question->save($id);


            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }
}