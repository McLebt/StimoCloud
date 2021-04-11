<?php

namespace pocketmine\cloud\network\protocol;

class DisconnectPacket extends DataPacket {

    public const NETWORK_ID = 2;

    public function pid() {
        return self::NETWORK_ID;
    }

    public const REASON_SERVER_SHUTDOWN = 0;
    public const REASON_WRONG_PASSWORD = 1;
    public const REASON_CLOUD_SHUTDOWN = 2;

    /** @var int */
    public $reason;

    protected function decodePayload() {
        $this->reason = $this->getString();
    }

    protected function encodePayload() {
        $this->putString($this->reason);
    }
}