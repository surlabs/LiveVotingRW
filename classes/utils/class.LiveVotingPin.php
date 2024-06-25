<?php

namespace utils;


use ilLiveVotingPlugin;
use LiveVoting;
use LiveVoting\Voting\LiveVotingParticipant;
use LiveVotingConfig;
use LiveVotingDatabase;
use LiveVotingException;

class LiveVotingPin
{
    const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;
    /**
     * @var string
     */
    protected string $pin = '';
    /**
     * @var bool
     */
    protected bool $use_lowercase = false;
    /**
     * @var bool
     */
    protected bool $use_uppercase = true;
    /**
     * @var bool
     */
    protected bool $use_numbers = true;
    /**
     * @var int
     */
    protected int $pin_length = 4;

    /**
     * xlvoPin constructor.
     *
     * @param string $pin
     */
    public function __construct(string $pin = '')
    {
        if (!$pin) {
            $this->generatePIN();
        } else {
            $this->setPin($pin);
        }
    }

    /**
     * @param string $pin
     * @param bool $force_not_format
     *
     * @return string
     * @throws LiveVotingException
     */
    public static function formatPin(string $pin, bool $force_not_format = false): string
    {

        //TODO: Es posible que esta configuración no exista
        if (!$force_not_format && LiveVotingConfig::get('use_serif_font_for_pins')) {
            $pin = '<span class="serif_font">' . $pin . '</span>';
        }

        return $pin;
    }


    /**
     * @param int $obj_id
     *
     * @return int
     * @throws LiveVotingException
     */
    public static function lookupPin(int $obj_id): int
    {
        $liveVoting = new LiveVoting($obj_id);

        return $liveVoting->getPin();
    }


    /**
     * @param string $pin
     * @param bool $safe_mode
     * @return int
     * @throws LiveVotingException
     */
    public static function checkPinAndGetObjId(string $pin, bool $safe_mode = true): int
    {
        return self::checkPinAndGetObjIdWithoutCache($pin, $safe_mode);
    }

    /**
     * @throws LiveVotingException
     */
    private static function checkPinAndGetObjIdWithoutCache(string $pin, bool $safe_mode = true): int
    {
        $database = new LiveVotingDatabase();
        $result = $database->select("rep_robj_xlvo_config_n", array("obj_id"), array("pin" => $pin));

        if (isset($result[0])) {
            $liveVoting = new LiveVoting($result[0]["obj_id"]);

            if (!$liveVoting->isOnline()) {
                if ($safe_mode) {
                    // TODO: Excepcion
                    throw new LiveVotingException('TODO: message');
                }
            }

            if (!$liveVoting->isAnonymous() && LiveVotingParticipant::getInstance()->isPINUser()) {
                if ($safe_mode) {
                    // TODO: Excepcion
                    throw new LiveVotingException('TODO: message');
                }
            }

            return $liveVoting->getId();
        }

        return 0;
    }



    /**
     *
     */
    protected function generatePIN()
    {
        $array = array();

        // numbers
        if ($this->isUseNumbers()) {
            for ($i = 48; $i < 58; $i++) {
                $array[] = chr($i);
            }
        }

        // lower case
        if ($this->isUseLowercase()) {
            for ($i = 97; $i <= 122; $i++) {
                $array[] = chr($i);
            }
        }

        // upper case
        if ($this->isUseUppercase()) {
            for ($i = 65; $i <= 90; $i++) {
                $array[] = chr($i);
            }
        }

        $pin = '';
        $pin_found = false;



        while (!$pin_found) {
            for ($i = 1; $i <= $this->getPinLength(); $i++) {
                $rnd = mt_rand(0, count($array) - 1);
                $pin .= $array[$rnd];
            }
            if (xlvoVotingConfig::where(array('pin' => $pin))->count() <= 0) {
                $pin_found = true;
            }
        }

        $this->setPin($pin);
    }


    /**
     * @return bool|string
     */
    public function getLastAccess()
    {
        return $this->getLastAccessWithoutCache();
    }


    /**
     * @return bool|string
     */
    private function getLastAccessWithoutCache()
    {
        $xlvoVotingConfig = xlvoVotingConfig::where(array('pin' => $this->getPin()))->first();

        if (!($xlvoVotingConfig instanceof xlvoVotingConfig)) {
            return false;
        }

        return $xlvoVotingConfig->getLastAccess();
    }


    /**
     * @return string
     */
    public function getPin(): string
    {
        return $this->pin;
    }


    /**
     * @param string $pin
     */
    public function setPin($pin)
    {
        $this->pin = $pin;
    }


    /**
     * @return boolean
     */
    public function isUseLowercase(): bool
    {
        return $this->use_lowercase;
    }


    /**
     * @param boolean $use_lowercase
     */
    public function setUseLowercase(bool $use_lowercase)
    {
        $this->use_lowercase = $use_lowercase;
    }


    /**
     * @return boolean
     */
    public function isUseUppercase(): bool
    {
        return $this->use_uppercase;
    }


    /**
     * @param boolean $use_uppercase
     */
    public function setUseUppercase(bool $use_uppercase)
    {
        $this->use_uppercase = $use_uppercase;
    }


    /**
     * @return boolean
     */
    public function isUseNumbers(): bool
    {
        return $this->use_numbers;
    }


    /**
     * @param boolean $use_numbers
     */
    public function setUseNumbers(bool $use_numbers)
    {
        $this->use_numbers = $use_numbers;
    }


    /**
     * @return int
     */
    public function getPinLength(): int
    {
        return $this->pin_length;
    }


    /**
     * @param int $pin_length
     */
    public function setPinLength(int $pin_length)
    {
        $this->pin_length = $pin_length;
    }
}