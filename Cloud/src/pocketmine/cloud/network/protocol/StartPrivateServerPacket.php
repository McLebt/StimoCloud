<?php
/* Copyright (c) 2020 xxAROX. All rights reserved. */
namespace pocketmine\cloud\network\protocol;

class StartPrivateServerPacket extends RequestPacket{
	public const NETWORK_ID = self::PACKET_START_PRIVATE_SERVER;

	/** @var string */
	public $template = "";

	/** @var string */
	public $requestId = "";

	/** @var string */
	public $owner    = "";



	/**
	 * Function decodePayload
	 * @return void
	 */
	protected function decodePayload(): void{
		$this->type = $this->getInt();
		$this->requestId = $this->getString();
		$this->template = $this->getString();
		$this->owner = $this->getString();
	}

	/**
	 * Function encodePayload
	 * @return void
	 */
	protected function encodePayload(): void{
		$this->putInt($this->type);
		$this->putString($this->requestId);
		$this->putString($this->template);
		$this->putString($this->owner);
	}
}
