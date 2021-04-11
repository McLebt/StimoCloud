<?php
namespace Bridge\cloudbridge\packets;
/**
 * Class AcceptConnectionPacket
 * @package Bridge\cloudbridge\packets
 * @author xxAROX
 * @date 02.08.2020 - 00:38
 * @project CloudServer
 */
class AcceptConnectionPacket extends Packet{
    public const NETWORK_ID = self::PACKET_ACCEPT_CONNECTION;

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
