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


namespace LiveVoting\platform\ilias;

use ilObjUser;

class DummyUser extends ilObjUser {
    /**
     * DummyUser constructor.
     */
    public function __construct()
    {

    }

    /**
     * Returns the language of the user.
     * This dummy only returns statically the "de" language code
     * because no other help packages are available atm. (27.10.2016)
     *
     * @return string returns the language code "de" without the quotes.
     */
    public function getLanguage():string
    {
        return self::dic()->language()->getLangKey();
    }


    /**
     * @return int
     */
    public function getId() : int
    {
        return 13;
    }


    /**
     * This dummy method returns statically false.
     *
     * @param string $a_keyword Preference name which will be ignored by this dummy function.
     *
     * @return string|null Returns constant null.
     */
    public function getPref(string $a_keyword): ?string
    {
        return null;
    }
}