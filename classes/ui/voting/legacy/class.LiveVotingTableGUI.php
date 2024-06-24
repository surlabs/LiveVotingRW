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

namespace LiveVoting\legacy;

use ilAdvancedSelectionListGUI;
use ilCheckboxInputGUI;
use ilCtrlException;
use ilException;
use ilFormPropertyGUI;
use ilLiveVotingPlugin;
use ilObjLiveVotingAccess;
use ilObjLiveVotingGUI;
use ilSelectInputGUI;
use ilTable2GUI;
use ilUtil;
use JsonException;
use LiveVoting\Js\xlvoJs;
use LiveVoting\QuestionTypes\xlvoQuestionTypes;
use LiveVoting\Utils\LiveVotingTrait;
use LiveVotingDatabase;
use LiveVotingException;
use LiveVotingQuestion;
use srag\CustomInputGUIs\LiveVoting\TextInputGUI\TextInputGUI;
use ilLegacyFormElementsUtil;

/**
 * Class liveVotingTableGUI
 *
 * @package LiveVoting\Voting
 * @author  Daniel Aemmer <daniel.aemmer@phbern.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class liveVotingTableGUI extends ilTable2GUI
{


    const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;
    const TBL_ID = 'tbl_xlvo';
    const LENGTH = 100;
    /**
     * @var ilObjLiveVotingGUI
     */
    protected ilObjLiveVotingGUI $voting_gui;
    /**
     * @var array
     */
    protected array $filter = array();
    /**
     * @var ilObjLiveVotingAccess
     */
    protected ilObjLiveVotingAccess $access;
    /**
     * @var ilLiveVotingPlugin
     */
    protected ilLiveVotingPlugin $pl;


    /**
     * @throws ilException
     * @throws LiveVotingException
     */
    public function __construct(ilObjLiveVotingGUI $a_parent_obj, $a_parent_cmd)
    {
        global $DIC;

        $this->voting_gui = $a_parent_obj;
        $this->access = new ilObjLiveVotingAccess();
        $this->pl = ilLiveVotingPlugin::getInstance();

        $DIC->ui()->mainTemplate()->addJavaScript($this->pl->getDirectory() . "/templates/js/libs/sortable.min.js");

        $this->setId(self::TBL_ID);
        $this->setPrefix(self::TBL_ID);
        $this->setFormName(self::TBL_ID);
        $DIC->ctrl()->saveParameter($a_parent_obj, $this->getNavParameter());

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setRowTemplate('tpl.tbl_voting.html', $this->pl->getDirectory());
        $this->setExternalSorting(true);
        $this->setExternalSegmentation(true);
        $this->initColums();
        //$this->addFilterItems();
        $this->parseData();

        $this->setFormAction($DIC->ctrl()->getFormAction($a_parent_obj));
        $this->addCommandButton('saveSorting', $this->txt('voting_save_sorting'));
    }


    /**
     * @param string $key
     *
     * @return string
     */
    protected function txt(string $key): string
    {
        return $this->pl->txt($key);
    }


/*    protected function addFilterItems()
    {
        $title = new TextInputGUI($this->txt('title'), 'title');
        $this->addAndReadFilterItem($title);

        $question = new TextInputGUI($this->txt('question'), 'question');
        $this->addAndReadFilterItem($question);

        $status = new ilSelectInputGUI($this->txt('status'), 'voting_status');
        $status_options = array(
            -1                          => '',
            xlvoVoting::STAT_INACTIVE   => $this->txt('status_' . xlvoVoting::STAT_INACTIVE),
            xlvoVoting::STAT_ACTIVE     => $this->txt('status_' . xlvoVoting::STAT_ACTIVE),
            xlvoVoting::STAT_INCOMPLETE => $this->txt('status_' . xlvoVoting::STAT_INCOMPLETE),
        );
        $status->setOptions($status_options);
        //		$this->addAndReadFilterItem($status); deativated at the moment

        $type = new ilSelectInputGUI($this->txt('type'), 'voting_type');
        $type_options = array(
            -1 => '',
        );

        foreach (xlvoQuestionTypes::getActiveTypes() as $qtype) {
            $type_options[$qtype] = $this->txt('type_' . $qtype);
        }

        $type->setOptions($type_options);
        $this->addAndReadFilterItem($type);
    }*/


    /**
     * @param ilFormPropertyGUI $item
     */
    protected function addAndReadFilterItem(ilFormPropertyGUI $item)
    {
        $this->addFilterItem($item);
        $item->readFromSession();
        if ($item instanceof ilCheckboxInputGUI) {
            $this->filter[$item->getPostVar()] = $item->getChecked();
        } else {
            $this->filter[$item->getPostVar()] = $item->getValue();
        }
    }


    /**
     * @param array $a_set
     * @throws LiveVotingException
     */
    protected function fillRow(array $a_set): void
    {
        $question = LiveVotingQuestion::loadQuestionById((int)$a_set['id']);
        $this->tpl->setVariable('TITLE', $question->getTitle());
        //->tpl->setVariable('DESCRIPTION', $this->shorten($question->getQuestion()));

        //$question = strip_tags("QUESTION TEST");

        //$question = $this->shorten($question);
        $this->tpl->setVariable('QUESTION', ilLegacyFormElementsUtil::prepareTextareaOutput($this->shorten($question->getQuestion()), true));
        $this->tpl->setVariable('TYPE', $this->txt('voting_type_'.$a_set['voting_type']));

        $voting_status = $this->getVotingStatus("STATUS");
        //		$this->tpl->setVariable('STATUS', $voting_status); // deactivated at the moment

        $this->tpl->setVariable('ID', "ID");

        $this->addActionMenu($question);
    }


    protected function initColums()
    {
        $this->addColumn('', 'position', '20px');
        $this->addColumn($this->txt('voting_title'));
        $this->addColumn($this->txt('voting_question'));
        $this->addColumn($this->txt('voting_type'));
        //		$this->addColumn($this->txt('status'));
        $this->addColumn($this->txt('actions'), '', '150px');
    }


    /**
     * @param LiveVotingQuestion $question
     * @throws ilCtrlException
     * @throws JsonException
     */
    protected function addActionMenu(LiveVotingQuestion $question)
    {
        global $DIC;
        $current_selection_list = new ilAdvancedSelectionListGUI();
        $current_selection_list->setListTitle($this->txt('common_actions'));
        $current_selection_list->setId('xlvo_actions_' . $question->getId());
        $current_selection_list->setUseImages(false);

        $DIC->ctrl()->setParameter($this->voting_gui, 'question_id', $question->getId());
        if ($this->access->hasWriteAccess()) {
            $current_selection_list->addItem($this->txt('voting_edit'), 'edit',$DIC->ctrl()
                ->getLinkTarget($this->voting_gui, 'edit'));
            $current_selection_list->addItem($this->txt('voting_reset'), 'confirmDelete', $DIC->ctrl()
                ->getLinkTarget($this->voting_gui, 'confirmDelete'));
            $current_selection_list->addItem($this->txt('voting_duplicate'), 'duplicate', $DIC->ctrl()
                ->getLinkTarget($this->voting_gui, 'duplicate'));
            $current_selection_list->addItem($this->txt('voting_duplicateToAnotherObject'), 'duplicateToAnotherObjectSelect', $DIC->ctrl()
                ->getLinkTarget($this->voting_gui, 'duplicateToAnotherObjectSelect'));
            $current_selection_list->addItem($this->txt('voting_delete'), 'confirmDelete', $DIC->ctrl()
                ->getLinkTarget($this->voting_gui, 'confirmDelete'));
        }
        $current_selection_list->getHTML();
        $this->tpl->setVariable('ACTIONS', $current_selection_list->getHTML());
    }


    /**
     * @throws LiveVotingException
     */
    protected function parseData()
    {
        // Filtern
        $this->determineOffsetAndOrder();
        $this->determineLimit();

        $database = new LiveVotingDatabase();
        $sorting_column = $this->getOrderField() ? $this->getOrderField() : 'position';
        $offset = $this->getOffset() ? $this->getOffset() : 0;

        $sorting_direction = $this->getOrderDirection();
        $num = $this->getLimit();

        $collection = $database->select("rep_robj_xlvo_voting_n", array(
            "obj_id" => $this->voting_gui->getObjId(),
        ), null, "ORDER BY ".$sorting_column." ".$sorting_direction." LIMIT ".$offset.", ".$num);



        foreach ($this->filter as $filter_key => $filter_value) {
            switch ($filter_key) {
                case 'title':
                case 'question':
                    if ($filter_value) {
                        $collection = $collection->where(array($filter_key => '%' . $filter_value . '%'), 'LIKE');
                    }
                    break;
                case 'voting_status':

                case 'voting_type':
                    if ($filter_value != "") {
                        $collection = $collection->where(array($filter_key => $filter_value));
                    }
                    break;
            }
        }

        $this->setData($collection);
    }


    /**
     * @param $voting_status
     *
     * @return string
     */
    protected function getVotingStatus($voting_status)
    {
        return $this->txt('status_' . $voting_status);
    }


    /**
     * @param string $question
     *
     * @return string
     */
    protected function shorten($question)
    {
        return strlen($question) > self::LENGTH ? substr($question, 0, self::LENGTH) . "..." : $question;
    }
}
