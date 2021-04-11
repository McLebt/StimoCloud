<?php
namespace pocketmine\cloud\network\protocol;

class SendPlayersMessagePacket extends Packet{
    public const NETWORK_ID = self::PACKET_SEND_PLAYERS_MESSAGE;

    /** @var string */
    public $message = "";
    /** @var int */
    public $includePrefix = 0;



	/**
	 * Function decodePayload
	 * @return void
	 */
    protected function decodePayload() {
        $this->message = $this->getString();
        $this->includePrefix = $this->getInt();
    }

	/**
	 * Function encodePayload
	 * @return void
	 */
    protected function encodePayload() {
        $this->putString($this->message);
        $this->putInt($this->includePrefix);
    }
}
