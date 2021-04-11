<?php

namespace pocketmine\cloud\network\protocol;

class AcceptConnectionPacket extends DataPacket {
    public const NETWORK_ID = 0x0003;

    /** @var string */
    public $string = "";


	/**
	 * Function pid
	 * @return int
	 */
    public function pid() {
        return self::NETWORK_ID;
    }

	/**
	 * Function decodePayload
	 * @return void
	 */
    protected function decodePayload() {
        $this->string = $this->getString();
    }

	/**
	 * Function encodePayload
	 * @return void
	 */
    protected function encodePayload() {
        $this->putString($this->string);
    }
}
