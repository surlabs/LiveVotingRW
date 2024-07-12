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
namespace LiveVoting\UI\Voting\Bar;


use ilTemplateException;

class LiveVotingBarInfoGUI extends LiveVotingAbstractBarUI implements LiveVotingGeneralBarUI
{
    /**
     * @var string
     */
    protected string $value;
    /**
     * @var string
     */
    protected string $label;


    /**
     * xlvoBarInfoGUI constructor.
     *
     * @param string $label
     * @param string $value
     */
    public function __construct($label, $value)
    {
        parent::__construct();
        $this->label = $label;
        $this->value = $value;
    }


    /**
     *
     * @throws ilTemplateException
     */
    protected function render()
    {
        parent::render();
        $this->tpl->setVariable('FREE_INPUT', $this->label . ": " . $this->value);
    }


    /**
     * @return string
     * @throws ilTemplateException
     */
    public function getHTML(): string
    {
        $this->render();

        return $this->tpl->get();
    }

}

