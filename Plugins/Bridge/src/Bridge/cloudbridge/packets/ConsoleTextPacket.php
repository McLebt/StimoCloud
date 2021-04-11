<?php
namespace Bridge\cloudbridge\packets;
/**
 * Class ConsoleTextPacket
 * @package Bridge\cloudbridge\packets
 * @author xxAROX
 * @date 02.08.2020 - 00:23
 * @project CloudServer
 */
class ConsoleTextPacket extends Packet{
    public const NETWORK_ID = Packet::PACKET_LOG;
	/** @var string */
    public $sender = "";
	/** @var string */
    public $message = "";



	/**
	 * Function decodePayload
	 * @return void
	 */
    protected function decodePayload() {
        $this->sender = $this->getString();
        $this->message = $this->getString();
    }

	/**
	 * Function encodePayload
	 * @return void
	 */
    protected function encodePayload() {
		$this->putString($this->sender);
		$this->putString($this->message);
    }
}
