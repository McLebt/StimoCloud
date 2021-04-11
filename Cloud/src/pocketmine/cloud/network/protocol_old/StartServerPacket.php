<?php

namespace pocketmine\cloud\network\protocol;

class StartServerPacket extends RequestPacket {

    public const NETWORK_ID = 5;

    public function pid() {
        return self::NETWORK_ID;
    }

    /** @var string */
    public $template = "";

    public $serverid = "";

    public $status = 0;

    protected function decodePayload() {
        $this->type = $this->getInt();
        $this->requestid = $this->getString();
        $this->template = $this->getString();
        $this->serverid = $this->getString();
        $this->status = $this->getInt();
    }

    protected function encodePayload() {
        $this->putInt($this->type);
        $this->putString($this->requestid);
        $this->putString($this->template);
        $this->putString($this->serverid);
        $this->putInt($this->status);
    }
}