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

/**
 * Class LiveVotingManageUI
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 * @ilCtrl_IsCalledBy  ilObjLiveVotingGUI: ilObjPluginGUI
 */
class LiveVotingManageUI
{
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

    public function __construct()
    {
        global $DIC;

        $this->plugin = ilLiveVotingPlugin::getInstance();
        $this->control = $DIC->ctrl();
        $this->request = $DIC->http()->request();
        $this->factory = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();
    }

    /**
     * @throws \ilCtrlException
     */
    public function showManage(): string{
        global $DIC;

        $f = $DIC->ui()->factory();
        $renderer = $DIC->ui()->renderer();
        $ico = $f->symbol()->icon()->standard('', '')->withSize('medium')->withAbbreviation('+');
        $image = $f->image()->responsive("src/UI/examples/Image/mountains.jpg", "Image source: https://stocksnap.io, Creative Commons CC0 license");
        $page = $f->modal()->lightboxImagePage($image, 'Mountains');
        $modal = $f->modal()->lightbox($page);

        $glyph = $f->symbol()->glyph()->add("#");

        $button = $f->button()->bulky($glyph, '<div style="margin-left:10px">'.$this->plugin->txt("voting_type_1").' <br/><small><muted>('.$this->plugin->txt("voting_type_1_info").')</muted></small></div>', $this->control->getLinkTargetByClass(ilObjLiveVotingGUI::class, 'selectedType1'));
        $button2 = $f->button()->bulky($glyph, '<div style="margin-left:10px">'.$this->plugin->txt("voting_type_2").' <br/><small><muted>('.$this->plugin->txt("voting_type_2_info").')</muted></small></div>', $this->control->getLinkTargetByClass(ilObjLiveVotingGUI::class, 'selectedType2'));
        $button3 = $f->button()->bulky($glyph, '<div style="margin-left:10px">'.$this->plugin->txt("voting_type_4").' <br/><small><muted>('.$this->plugin->txt("voting_type_4_info").')</muted></small></div>', $this->control->getLinkTargetByClass(ilObjLiveVotingGUI::class, 'selectedType4'));
        $button4 = $f->button()->bulky($glyph, '<div style="margin-left:10px">'.$this->plugin->txt("voting_type_5").' <br/><small><muted>('.$this->plugin->txt("voting_type_5_info").')</muted></small></div>', $this->control->getLinkTargetByClass(ilObjLiveVotingGUI::class, 'selectedType5'));
        $button5 = $f->button()->bulky($glyph, '<div style="margin-left:10px">'.$this->plugin->txt("voting_type_6").' <br/><small><muted>('.$this->plugin->txt("voting_type_6_info").')</muted></small></div>', $this->control->getLinkTargetByClass(ilObjLiveVotingGUI::class, 'selectedType6'));


        $uri = new \ILIAS\Data\URI('https://ilias.de');
        $link = $f->link()->bulky($ico->withAbbreviation('>'), 'Link', $uri);
        $divider = $f->divider()->horizontal();

        $items = [
            $f->menu()->sub($this->plugin->txt('voting_add'), [$button, $button2, $button3, $button4, $button5]),

            $f->menu()->sub($this->plugin->txt('voting_reset_all'), [
                $f->menu()->sub('Otter', [$button, $link]),
                $f->menu()->sub('Mole', [$button, $link]),
                $divider,
                $f->menu()->sub('Deer', [$button, $link])
            ])
        ];

        $dd = $f->menu()->drilldown('Manage Votings (NO TRANSLATED)', $items);

        return $renderer->render($dd);

    }

    public function renderSelectTypeForm(): string
    {
        global $DIC;

        $form_fields = [];

        $radio = $this->factory->input()->field()->radio($this->plugin->txt("voting_type"))
            ->withOption('type_1', $this->plugin->txt("voting_type_1"), $this->plugin->txt("voting_type_1_info"))
            ->withOption('type_2', $this->plugin->txt("voting_type_2"), $this->plugin->txt("voting_type_2_info"))
            ->withOption('type_4', $this->plugin->txt("voting_type_4"), $this->plugin->txt("voting_type_4_info"))
            ->withOption('type_5', $this->plugin->txt("voting_type_5"), $this->plugin->txt("voting_type_5_info"))
            ->withOption('type_6', $this->plugin->txt("voting_type_6"), $this->plugin->txt("voting_type_6_info"))
            ->withRequired(true);

        $form_fields[] = $radio;


        $form = $this->factory->input()->container()->form()->standard('#', $form_fields);

        return $DIC->ui()->renderer()->render($form);

    }

    public function renderSelectType1Form(): string
    {
        global $DIC;
        try {
            $form_questions = [];

            $field_title = $this->factory->input()->field()->text(
                $this->plugin->txt('voting_title'))
                ->withValue("TEST")
                ->withRequired(true);
/*                ->withAdditionalTransformation($DIC->refinery()->custom()->transformation(
                    function ($v) use ($object) {

                    }
                ));*/


            $form_questions["title"] = $field_title;


            $field_question = $this->factory->input()->field()->textarea(
                $this->plugin->txt('voting_question'))
                ->withValue("TEST")
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

            $field_input = $this->factory->input()->field()->text(
                $this->plugin->txt('qtype_1_options'))
                ->withValue("TESTS")
                ->withOnLoadCode(function ($id) {
                    return "xlvo.initMultipleInputs('".$id."')";
                })
                ->withRequired(true);


            $form_answers["input"] = $field_input;

            $field_hidden = $this->factory->input()->field()->text("test")
                ->withValue("")
                ->withOnLoadCode(function ($id) {
                    return "xlvo.initHiddenInput('".$id."')";
                })
                ->withLabel('options');

            $form_answers["hidden"] = $field_hidden;


            $section_answers = $this->factory->input()->field()->section($form_answers, $this->plugin->txt("qtype_form_header"), "");

           $sections =  [
                "config_question" => $section_questions,
                "config_answers" => $section_answers
            ];

            $form_action = $this->control->getFormActionByClass(ilObjLiveVotingGUI::class, "selectedType1");

            $DIC->ui()->mainTemplate()->addJavaScript($this->plugin->getDirectory() . "/templates/js/xlvo.js");


            return $this->renderForm($form_action, $sections);


        } catch (Exception $e) {
            throw new ilException($e->getMessage());
        }
    }


    /**
     * @throws ilHtmlPurifierNotFoundException
     */
    private function renderForm(string $form_action, array $sections): string
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



        $field_question = $this->factory->legacy($form->getHTML());

        $modal = $this->factory->modal()->roundtrip('My Modal 1', $field_question);
        $modal = $modal->withCloseWithKeyboard(false);
        $button1 = $this->factory->button()->standard('Open Modal 1', '#')
            ->withOnClick($modal->getShowSignal());
        //Create the form
        $form = $this->factory->input()->container()->form()->standard(
            $form_action,
            $sections,
        );

        $saving_info = "";

        //Check if the form has been submitted
        if ($this->request->getMethod() == "POST") {
            $form = $form->withRequest($this->request);
            $result = $form->getData();
            $options = json_decode($result["config_answers"]["hidden"]);
            dump($options, $options[0], $options[1]);
            exit;
            //$saving_info = $this->save();


        }

        return $saving_info . $this->renderer->render($form);
    }
}