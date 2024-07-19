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

use ilException;
use ilTemplateException;

final class LiveVotingBarGroupingCollectionUI extends LiveVotingBarCollectionUI
{
    /**
     * @var LiveVotingBarFreeTextUI[] $bars
     */
    private array $bars = [];
    /**
     * @var bool
     */
    private bool $removable = false;
    /**
     * @var bool $rendered
     */
    private bool $rendered = false;
    /**
     * @var bool $sorted
     */
    private bool $sorted = false;


    /**
     * Adds a bar to the grouping collection.
     *
     * @param LiveVotingGeneralBarUI $bar_gui
     *
     * @return void
     * @throws ilException If the bars are already rendered or the given type is not compatible
     *                     with the collection.
     */
    public function addBar(LiveVotingGeneralBarUI $bar_gui)
    {
        $this->checkCollectionState();

        if ($bar_gui instanceof LiveVotingBarFreeTextUI) {
            $bar_gui->setRemovable($this->isRemovable());
            $this->bars[] = $bar_gui;
        } else {
            throw new ilException('$bar_gui must a type of xlvoBarFreeInputsGUI.');
        }
    }


    /**
     * @param bool $enabled
     */
    public function sorted(bool $enabled)
    {
        $this->sorted = $enabled;
    }


    /**
     * Render the template.
     * After the rendering process the bar object frees all resources and is no longer usable.
     *
     * @return string
     * @throws ilException If the bars are already rendered.
     */
    public function getHTML(): string
    {

        $this->checkCollectionState();

        $this->renderVotersAndVotes();

        $bars = null;
        if ($this->sorted) {
            $bars = $this->sortBarsByFrequency($this->bars);
        } else {
            $bars = $this->makeUniqueArray($this->bars);
        }

        //render the bars on demand
        foreach ($bars as $bar) {
            $count = $this->countItemOccurence($this->bars, $bar);
            $this->renderBar($bar, $count);
        }

        if (count($this->bars) === 0) {
            $this->tpl->touchBlock('bar');
        }
        unset($this->bars);
        $this->rendered = true;

        return $this->tpl->get();
    }


    /**
     * Add a solution to the collection.
     *
     * @param string $html The html which should be displayed as the solution.
     *
     * @return void
     * @throws ilException If the bars are already rendered.
     */
    public function addSolution($html): void
    {
        $this->checkCollectionState();
        parent::addSolution($html);
    }


    /**
     * Set the total votes of this question.
     *
     * @param int $total_votes The total votes done.
     *
     * @return void
     * @throws ilException If the bars are already rendered.
     */
    public function setTotalVotes(int $total_votes): void
    {
        $this->checkCollectionState();
        parent::setTotalVotes($total_votes);
    }


    /**
     * Indicates if the voters should be shown by the collection.
     *
     * @param bool $show_total_votes Should the total votes be displayed?
     *
     * @return void
     * @throws ilException If the bars are already rendered.
     */
    public function setShowTotalVotes(bool $show_total_votes): void
    {
        $this->checkCollectionState();
        parent::setShowTotalVotes($show_total_votes);
    }


    /**
     * Set the number of the voter participating at this question.
     *
     * @param int $total_voters The number of voters.
     *
     * @return void
     * @throws ilException If the bars are already rendered.
     */
    public function setTotalVoters(int $total_voters)
    {
        $this->checkCollectionState();
        parent::setTotalVoters($total_voters);
    }


    /**
     * @param bool $show_total_voters
     *
     * @return void
     * @throws ilException If the bars are already rendered.
     */
    public function setShowTotalVoters(bool $show_total_voters)
    {
        $this->checkCollectionState();
        parent::setShowTotalVoters($show_total_voters);
    }


    /**
     * This method renders the bars.
     *
     * @param LiveVotingBarFreeTextUI $bar The bar which should be rendered into the template.
     * @param int $count The times the bar got grouped.
     *
     * @return void
     * @throws ilTemplateException
     */
    private function renderBar(LiveVotingBarFreeTextUI $bar, $count)
    {
        $bar->setOccurrences($count);

        $this->tpl->setCurrentBlock('bar');
        $this->tpl->setVariable('BAR', $bar->getHTML());
        $this->tpl->parseCurrentBlock();
    }


    /**
     * Count the occurrences of bar within the given collection of bar.
     *
     * @param LiveVotingBarFreeTextUI[] $bars The collection which should be searched
     * @param LiveVotingBarFreeTextUI   $bar
     *
     * @return int The times bar was found in bars.
     */
    private function countItemOccurence(array $bars, LiveVotingBarFreeTextUI $bar): int
    {
        $count = 0;
        foreach ($bars as $entry) {
            if ($bar->equals($entry)) {
                $count++;
            }
        }

        return $count;
    }


    /**
     * Filter the array by freetext input.
     * The filter is case insensitive.
     *
     * @param LiveVotingBarFreeTextUI[] $bars The array which should be filtered.
     *
     * @return LiveVotingBarFreeTextUI[] The new array which contains only unique bars.
     */
    private function makeUniqueArray(array $bars): array
    {
        /**
         * @var LiveVotingBarFreeTextUI $filter
         */
        $uniqueBars = [];

        while (count($bars) > 0) {
            $bar = reset($bars);
            $bars = array_filter($bars, function ($item) use ($bar) {
                return !$bar->equals($item);
            });
            $uniqueBars[] = $bar;
        }

        return $uniqueBars;
    }


    /**
     * Checks the collection state. If the collection is no longer
     * usable a ilException is thrown. This method does nothing, if
     * the collection is ready to go.
     *
     * @return void
     * @throws ilException If the bars are already rendered.
     */
    private function checkCollectionState()
    {
        if ($this->rendered) {
            throw new ilException("The bars are already rendered, therefore the collection can't be modified or rendered.");
        }
    }


    /**
     * Creates a copy with unique elements of the supplied array and sorts the content afterwards.
     * The current sorting is descending.
     *
     * @param LiveVotingBarFreeTextUI[] $bars The array of bars which should be sorted.
     *
     * @return LiveVotingBarFreeTextUI[] Descending sorted array.
     */
    private function sortBarsByFrequency(array $bars): array
    {
        //dirty -> should be optimised in the future.

        $unique = $this->makeUniqueArray($bars);

        //[[count, bar], [count, bar]]
        $result = [];

        foreach ($unique as $item) {
            $result[] = [$this->countItemOccurence($bars, $item), $item];
        }

        //sort elements
        usort($result, function ($array1, $array2) {
            if ($array1[0] == $array2[0]) {
                return 0;
            }

            if ($array1[0] < $array2[0]) {
                return 1;
            }

            return -1;
        });

        //flatten the array to the bars
        $sortedResult = [];

        foreach ($result as $entry) {
            $sortedResult[] = $entry[1];
        }

        return $sortedResult;
    }


    /**
     * @return bool
     */
    public function isRemovable(): bool
    {
        return $this->removable;
    }


    /**
     * @param bool $removable
     */
    public function setRemovable(bool $removable)
    {
        $this->removable = $removable;
    }

}

