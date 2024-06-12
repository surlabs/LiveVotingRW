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

use ilCtrlInterface;
use ILIAS\UI\Factory;
use ilLiveVotingPlugin;
use ilPlugin;
use ilSystemStyleException;
use ilTemplate;
use ilTemplateException;

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
    public function showManage(): string{
        global $DIC;

        return "TEST";

    }

    public function renderSelectTypeForm(): string{
        global $DIC;
        $this->factory = $DIC->ui()->factory();
        $this->control = $DIC->ctrl();
        $this->plugin = ilLiveVotingPlugin::getInstance();

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
}