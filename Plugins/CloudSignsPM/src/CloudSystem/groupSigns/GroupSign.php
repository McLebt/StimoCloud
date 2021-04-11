<?php


namespace CloudSystem\groupSigns;


use CloudSystem\CloudSystem;
use mysql_xdevapi\Exception;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\tile\Sign;

class GroupSign
{

    /** @var Level */
    private $level;

    /** @var string */
    private $group_name;

    /** @var Vector3 */
    private $vector3;

    /** @var array */
    private $servers;

    /** @var null | string */
    private $broadcast_server = null;

    public function __construct(Sign $sign,string $group_name, array $servers)
    {
        $this->servers = $servers;
        $this->group_name = $group_name;
        $this->level = $sign->getLevel();
        $this->vector3 = $sign->asVector3();
    }

    /**
     * @return string|null
     */
    public function getBroadcastServer(): ?string
    {
        return $this->broadcast_server;
    }

    /**
     * @return array
     */
    public function getServers(): array
    {
        return $this->servers;
    }

    /**
     * @return string
     */
    public function getGroupName(): string
    {
        return $this->group_name;
    }

    /**
     * @return null|Sign
     */
    public function getSign(): ?Sign
    {
        $tile = $this->getLevel()->getTile($this->getVector3());
        if ($tile instanceof Sign) return $tile;
        return null;
    }

	/**
	 * Function updateSign
	 * @return void
	 * @author Florian H.
	 */
    public function updateSign()
    {
        if ($this->getBroadcastServer() == null or !$this->checkServerStatus($this->getBroadcastServer())) {
            $broadcast_server_old = $this->getBroadcastServer();
            foreach ($this->getServers() as $server) {
                if ($this->checkServerStatus($server) and !GroupSigns::isServerBroadcast($this->getGroupName(), $server)) {
                    if ($this->getBroadcastServer() != null) {
                        GroupSigns::removeBroadcastServers($this->getGroupName(), $this->getBroadcastServer());
                    }
                    $this->broadcast_server = $server;
                    GroupSigns::setBroadcastGroupServers($this->getGroupName(), $this->getBroadcastServer());
                    break;
                }
            }
            if ($this->getBroadcastServer() == $broadcast_server_old and $this->getBroadcastServer() != null) {
                GroupSigns::removeBroadcastServers($this->getGroupName(), $broadcast_server_old);
                $this->broadcast_server = null;
            }
        }
        $this->reloadSignContent();
    }

    public function reloadSignContent()
    {
        if ($this->getSign() == null) return;
        if ($this->getBroadcastServer() == null) {
            $this->getSign()->setText(
                $this->group_name,
                "§0",
                "§cSearch Server",
                "§0"

            );
        } else {
            $server = CloudSystem::getInstance()->getConfigFile($this->getBroadcastServer());
            if ($server == null) return;
            $server = $server->getAll();
            if (!empty($server["count"]) or !empty($server["max"])) {
                $count = $server["count"];
                $max = $server["max"];

                if ($count >= $max) {
                    $this->getSign()->setText(
                        $this->getBroadcastServer(),
                        "§0[§6Lobby§0]",
                        "§7" . $count . " §f/ §c" . $max,
                        "§7---"
                    );
                } else {
                    $this->getSign()->setText(
                        $this->getBroadcastServer(),
                        "§0[§2Lobby§0]",
                        "§7" . $count . " §f/ §c" . $max,
                        "§7---"
                    );
                }
            }
        }
    }

    /**
     * @param string $server
     * @return bool
     */
    private function checkServerStatus(string $server) : bool
    {

        $status = CloudSystem::getInstance()->getConfigFile($server);
        if ($status !== null) {
            if (!empty($status->get("offline") or !empty($status->get("ingame")))) {
                if ($status == null or $status->get("offline") == true or $status->get("ingame") == true) return false;
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return Level
     */
    public function getLevel(): Level
    {
        return $this->level;
    }
    /**
     * @return Vector3
     */
    public function getVector3(): Vector3
    {
        return $this->vector3;
    }

}