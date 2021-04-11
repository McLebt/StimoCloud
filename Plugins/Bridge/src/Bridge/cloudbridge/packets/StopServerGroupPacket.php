<?php
/* Copyright (c) 2020 xxAROX. All rights reserved. */
namespace Bridge\cloudbridge\packets;


/**
 * Class StartServerPacket
 * @package Bridge\cloudbridge\network\protocol
 * @author xxAROX
 * @date 31.07.2020 - 08:12
 * @project StimoCloud
 */
class StopServerGroupPacket extends RequestPacket{
	public const NETWORK_ID = self::PACKET_STOP_GROUP;

	/** @var string */
	public $template = "";
	/** @var string */
	public $requestId = "";



	/**
	 * Function decodePayload
	 * @return void
	 */
	protected function decodePayload(): void{
		$this->type = $this->getInt();
		$this->requestId = $this->getString();
		$this->template = $this->getString();
	}

	/**
	 * Function encodePayload
	 * @return void
	 */
	protected function encodePayload(): void{
		$this->putInt($this->type);
		$this->putString($this->requestId);
		$this->putString($this->template);
	}
}
