<?php

namespace pocketmine\cloud;

use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\MainLogger;
use pocketmine\utils\UUID;

class CloudServer {

    private $cloud;

    /** @var Template */
    private $template;
    /** @var int */
    public $id;
    /** @var string */
    public $owner;
    /** @var UUID */
    protected $uuid = null;
    /** @var int */
    private $port;
    /** @var int */
    public $created;
    /** @var null | Config */
    private static $group_config = null;

    public function __construct(Cloud $cloud, Template $template, int $id, String $owner, int $port) {
        $this->cloud = $cloud;
        $this->template = $template;
        $this->id = $id;
        $this->owner = $owner;
        $this->port = $port;
        $this->created = time();
        $this->uuid = UUID::fromRandom();
    }

    /**
     * @return int
     */
    public function getPort(): int {
        return $this->port;
    }

	/**
	 * Function getUuid
	 * @return UUID
	 */
	public function getUuid(): UUID{
		return $this->uuid;
	}
    /**
     * @return Template
     */
    public function getTemplate(): Template {
        return $this->template;
    }

    public function getID() {
        return $this->template->getName()."-".$this->id;
    }
    
    public function getOwner() {
        return $this->template->getName()."-".$this->owner;
    }

    public function getFolder():string {
        return $this->cloud->getServerFolder().$this->getID()."/";
    }
    
    public function getPFolder():string {
        return $this->cloud->getServerFolder().$this->getOwner()."/";
    }

	public function getCrashdumps():string {
    	if (is_dir($this->cloud->getServerFolder().$this->getID()."/crashdumps/")) {
			return $this->cloud->getServerFolder() . $this->getID() . "/crashdumps/";
		} else {
    		return false;
		}
	}

	public function getPCrashdumps():string {
		if (is_dir($this->cloud->getServerFolder().$this->getOwner()."/crashdumps/")) {
			return $this->cloud->getServerFolder() . $this->getOwner() . "/crashdumps/";
		} else {
			return false;
		}
	}

    public function getProperties():Config{
        return new Config($this->getFolder()."server.properties", Config::PROPERTIES);
    }

    public function getPProperties():Config{
        return new Config($this->getPFolder()."server.properties", Config::PROPERTIES);
    }

    public static function getGroupConfig()
    {
        if (self::$group_config != null) return self::$group_config;
        self::$group_config = new Config('/root/Server/CloudDatenbank/server_groups.json', Config::JSON);
        return self::$group_config;
    }

	public function registerServer(String $serverName, String $ip, int $port){
		$this->passProxyCommand("registerserver " . $serverName . " " . $ip . " " . $port);
	}

	public function unregisterServer(String $serverName){
		$this->passProxyCommand("unregisterserver " . $serverName);
	}

    /**
     * @return array
     */
    public static function getGroups()
    {
        return array_keys(self::getGroupConfig()->getAll());
    }

    public static function getGroupServers(string $group)
    {
        return self::getGroupConfig()->get($group);
    }

    /**
     * @param string $group
     * @return bool
     */
    public static function isGroup(string $group)
    {
        return self::getGroupConfig()->exists($group);
    }

    /**
     * @param string $group
     */
    public static function addGroup(string $group)
    {
        self::getGroupConfig()->set($group, []);
        self::getGroupConfig()->save();
    }

    /**
     * @param string $group
     */
    public static function removeGroup(string $group)
    {
        if (!self::isGroup($group)) return;
        self::getGroupConfig()->remove($group);
        self::getGroupConfig()->save();
    }

    /**
     * @param string $server
     * @param string $group
     * @return bool
     */
    public static function isServerInGroup(string $group, string $server)
    {
        if (!self::isGroup($group)) return false;
        return in_array($server, self::getGroupServers($group));
    }

    /**
     * @param string $server
     * @param string $group
     */
    public static function addServerToGroup(string $group, string $server)
    {
        if (!self::isGroup($group)) return;
        if (self::isServerInGroup($group, $server)) return;
        self::getGroupConfig()->set($group, array_merge(self::getGroupServers($group), [$server]));
        self::getGroupConfig()->save();
        self::getGroupConfig()->reload();
    }

    /**
     * @param string $server
     * @param string $group
     */
    public static function removeServerFromGroup(string $group, string $server)
    {
        if (!self::isGroup($group)) return;
        if (!self::isServerInGroup($group, $server)) return;
        $array = self::getGroupServers($group);
        unset($array[array_search($server, $array)]);
        self::getGroupConfig()->set($group, $array);
        self::getGroupConfig()->save();
        self::getGroupConfig()->reload();
    }

    public function saveCrashdumps()
	{
		if ($this->getCrashdumps() !== false) {
			if (!empty($this->getCrashdumps())) {
				passthru("cp -r " . $this->getCrashdumps() . "* /home/mcpe/CloudServer/server_crashdumps/" . $this->getID());
			}
		}
	}

	public function savePCrashdumps()
	{
		if ($this->getPCrashdumps() !== false) {
			if (!empty($this->getPCrashdumps())) {
				passthru("cp -r " . $this->getPCrashdumps() . "* /home/mcpe/CloudServer/server_crashdumps/" . $this->getOwner());
			}
		}
	}

    public function startServer():void
    {
    	$server = $this->getID();
    	$ip = "127.0.0.1";
    	$port = (int)$this->getProperties()->get("server-port");

    	$this->killScreen();

        try {
			if (is_dir($this->getFolder())) {
				//AddGroup
				if (!self::isGroup($this->template->getName())) {
					self::addGroup($this->template->getName());
					$this->cloud->getServer()->getLogger()->info(Options::PREFIX . "§aGroup added to Database§7!");
				}

				//RemoveServer
				if (self::isServerInGroup($this->template->getName(), $this->getID())) {
					self::removeServerFromGroup($this->template->getName(), $this->getID());
					$this->cloud->getServer()->getLogger()->info(Options::PREFIX . "§aRemoved Server from Database§7!");
				}

				//AddServer
				if (!self::isServerInGroup($this->template->getName(), $this->getID())) {
					self::addServerToGroup($this->template->getName(), $this->getID());
					$this->cloud->getServer()->getLogger()->info(Options::PREFIX . "§aAdded Server to Database§7!");
				}

				$this->cloud->getServer()->getLogger()->info(Options::PREFIX . "§aStarting server§e " . $this->getID());
				passthru("chmod 0777 " . $this->getFolder() . "start.sh");
				passthru("tmux new-session -d -s " . $this->getID() . " '" . $this->getFolder() . "start.sh'");
				$this->unregisterServer($server);
				$this->registerServer($server, $ip, $port);

				sleep(1);

			} else {
				MainLogger::getLogger()->info(Options::PREFIX . "§cError whilst starting server§7!\n§cServer§7-§cFolder don't exists§7!");
			}
        } catch (\ErrorException $e) {
            var_dump($e);
        }
    }
    
    public function startPServer():void
    {
		$server = $this->getOwner();
		$ip = "127.0.0.1";
		$port = (int)$this->getPProperties()->get("server-port");

		$this->killPScreen();

        try {
			if (is_dir($this->getPFolder())) {
				//AddGroup
				if (!self::isGroup($this->template->getName())) {
					self::addGroup($this->template->getName());
					$this->cloud->getServer()->getLogger()->info(Options::PREFIX . "§aGroup added to Database§7!");
				}

				//RemoveServer
				if (self::isServerInGroup($this->template->getName(), $this->getOwner())) {
					self::removeServerFromGroup($this->template->getName(), $this->getOwner());
					$this->cloud->getServer()->getLogger()->info(Options::PREFIX . "§aRemoved Server from Database§7!");
				}

				//AddServer
				if (!self::isServerInGroup($this->template->getName(), $this->getOwner())) {
					self::addServerToGroup($this->template->getName(), $this->getOwner());
					$this->cloud->getServer()->getLogger()->info(Options::PREFIX . "§aAdded Server to Database§7!");
				}

				$this->cloud->getServer()->getLogger()->info(Options::PREFIX . "§aStarting server§e " . $this->getOwner());
				passthru("chmod 0777 " . $this->getPFolder() . "start.sh");
				passthru("tmux new-session -d -s " . $this->getOwner() . " '" . $this->getPFolder() . "start.sh'");
				$this->unregisterServer($server);
				$this->registerServer($server, $ip, $port);

				sleep(1);

			} else {
				MainLogger::getLogger()->info(Options::PREFIX . "§cError whilst starting server§7!\n§cServer§7-§cFolder don't exists§7!");
			}
        } catch (\ErrorException $e) {
            var_dump($e);
        }
    }

    public function stopServer():void
	{
		$server = $this->getID();

		try {
			$this->cloud->getServer()->getLogger()->info(Options::PREFIX . "§cStopping server§e " . $this->getID());

            $this->passCommand("shutdown");

			$socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
			$state = socket_connect($socket, "127.0.0.1", $this->getPort()+1);
			var_dump($state);
			if ($state) {
				socket_close($socket);
			}

			if (is_dir("/root/Server/CloudDatenbank/temp/")) {
				if (is_file("/root/Server/CloudDatenbank/temp/{$this->getID()}.json")) {
					$pid_cfg = new Config("/root/Server/CloudDatenbank/temp/{$this->getID()}.json", Config::JSON);
					if (isset($pid_cfg->getAll()["mypid"])) {
						$pid = (int)$pid_cfg->getAll()["mypid"];
						if (is_numeric($pid)) {
							exec("kill -9 $pid");
						}
					}
				}
			}

			$this->unregisterServer($server);

			//RemoveServer
			if (self::isServerInGroup($this->template->getName(), $this->getID())) {
				self::removeServerFromGroup($this->template->getName(), $this->getID());
				$this->cloud->getServer()->getLogger()->info(Options::PREFIX . "§aRemoved Server from Database§7!");
			}
			$this->cloud->getServer()->getLogger()->info(Options::PREFIX . "§cDeleting server§e " . $this->getID());
			$this->deleteServer();
			$this->template->unregisterServer($this);

			$this->killScreen();

		} catch (\ErrorException $e) {
			Server::getInstance()->getLogger()->logException($e);
		}
	}
    
    public function stopPServer():void
	{
		$server = $this->getOwner();

		try {
			$this->cloud->getServer()->getLogger()->info(Options::PREFIX . "§cStopping server§e " . $this->getOwner());

			$this->passPCommand("shutdown");
			$socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
			$state = socket_connect($socket, "127.0.0.1", $this->getPort()+1);
			var_dump($state);
			if ($state) {
				socket_close($socket);
			}

			if (is_dir("/root/Server/CloudDatenbank/temp/")) {
				if (is_file("/root/Server/CloudDatenbank/temp/{$this->getOwner()}.json")) {
					$pid_cfg = new Config("/root/Server/CloudDatenbank/temp/{$this->getOwner()}.json", Config::JSON);
					if (isset($pid_cfg->getAll()["mypid"])) {
						$pid = (int)$pid_cfg->getAll()["mypid"];
						if (is_numeric($pid)) {
							exec("kill -9 $pid");
						}
					}
				}
			}

			$this->unregisterServer($server);

			//RemoveServer
			if (self::isServerInGroup($this->template->getName(), $this->getOwner())) {
				self::removeServerFromGroup($this->template->getName(), $this->getOwner());
				$this->cloud->getServer()->getLogger()->info(Options::PREFIX . "§aRemoved Server from Database§7!");
			}
			$this->cloud->getServer()->getLogger()->info(Options::PREFIX . "§cDeleting server§e " . $this->getOwner());
			$this->deletePServer();
			$this->template->unregisterPServer($this);

			$this->killPScreen();

		} catch (\ErrorException $e) {
			Server::getInstance()->getLogger()->logException($e);
		}
	}

    public function copyTemplate():void{

    	$this->passCommand("save-all");

		try {
			$path = Server::getInstance()->getDataPath();
			$this->cloud->getServer()->getLogger()->info(Options::PREFIX . "§aSave server§e " . $this->getID());
			if (is_dir("{$path}templates/") && is_dir($this->getFolder())) {
				passthru("rm -r {$path}templates/" . $this->template->getName() . "/worlds/");
				passthru("mkdir {$path}templates/" . $this->template->getName() . "/worlds/");
				passthru("cp -r " . $this->getFolder() . "worlds/* {$path}templates/" . $this->template->getName() . "/worlds/");
				passthru("rm {$path}templates/" . $this->template->getName() . "/ops.txt");
				passthru("rm {$path}templates/" . $this->template->getName() . "/server.log");
				passthru("rm -r {$path}templates/" . $this->template->getName() . "/players/");
				passthru("rm -r {$path}templates/" . $this->template->getName() . "/crashdumps/");
				$this->cloud->getServer()->getLogger()->info(Options::PREFIX . "§aServer saved§8.");
			} else {
				$this->cloud->getServer()->getLogger()->info(Options::PREFIX . "§cError whilst saving files§7!§c Folder don' exists§7!");
			}
		} catch (\ErrorException $e) {
			Server::getInstance()->getLogger()->logException($e);
		}
	}

    public function passCommand(string $command):void
    {
		passthru("tmux send -t " . $this->getID() . " " . $command . " ENTER");
    }
    
    public function passPCommand(string $command):void
    {
		passthru("tmux send -t " . $this->getOwner() . " " . $command . " ENTER");
    }

    public function deleteServer():void
    {
		$this->saveCrashdumps();
		$this->cloud->getServer()->getLogger()->info(Options::PREFIX . "§cSaving crashdumps of Server§e " . $this->getID());
		$this->cloud->getServer()->getLogger()->info(Options::PREFIX . "§cDeleting server§e " . $this->getID());
		if (is_dir($this->getFolder())) {
			passthru("rm -r " . $this->getFolder());
		}
		$this->template->unregisterServer($this);
    }
    
    public function deletePServer():void
    {
		$this->savePCrashdumps();
		$this->cloud->getServer()->getLogger()->info(Options::PREFIX . "§cSaving crashdumps of Server§e " . $this->getOwner());
		$this->cloud->getServer()->getLogger()->info(Options::PREFIX . "§cDeleting server§e " . $this->getOwner());
		if (is_dir($this->getPFolder())) {
			passthru("rm -r " . $this->getPFolder());
		}
		$this->template->unregisterPServer($this);
    }

    public function passProxyCommand(string $command):void
    {
		passthru("screen -S Proxy -X stuff '" . $command . "
        '");
    }

    public function killScreen():void
    {
		passthru("tmux kill-session -t " . $this->getID());
    }
    
    public function killPScreen():void
    {
		passthru("tmux kill-session -t " . $this->getOwner());
    }

    //Query stuff
    private $playerCount = null;

    /**
     * @return null
     */
    public function getPlayerCount() {
        return $this->playerCount;
    }

    /**
     * @param null $playerCount
     */
    public function setPlayerCount($playerCount): void {
        $this->playerCount = $playerCount;
    }

    public function isServerConnected(): bool{
		return (in_array($this->getID(), $this->cloud->socket->clients));
	}

	public function isPServerConnected(): bool{
		return (in_array($this->getOwner(), $this->cloud->socket->clients));
	}
}
