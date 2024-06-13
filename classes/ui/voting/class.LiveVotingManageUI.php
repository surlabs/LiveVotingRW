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
use ilObjLiveVotingGUI;
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

    /**
     * @throws \ilCtrlException
     */
    public function showManage(): string{
        global $DIC;
        $this->plugin = ilLiveVotingPlugin::getInstance();
        $this->control = $DIC->ctrl();

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