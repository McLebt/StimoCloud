<?php
namespace CloudSystem\groupSigns;
use CloudSystem\CloudSystem;
use pocketmine\math\Vector3;
use pocketmine\tile\Sign;
use pocketmine\utils\Config;


class GroupSigns{
	/** @var array */
	private static $broadcast_group_servers = [];
	/** @var GroupSign[] */
	private static $group_signs = [];
	/** @var Config | null */
	private static $config = null;
	/** @var null | Config */
	private static $group_config = null;

	public static function getGroupSignsConfig(){
		if (self::$config != null)
			return self::$config;
		self::$config = new Config('/root/Server/CloudDatenbank/group_signs.json', Config::JSON);
		return self::$config;
	}

	public static function getGroupConfig(){
		if (self::$group_config != null)
			return self::$group_config;
		self::$group_config = new Config('/root/Server/CloudDatenbank/server_groups.json', Config::JSON);
		return self::$group_config;
	}

	/**
	 * @return array
	 */
	public static function getBroadcastGroupServers(): array{
		return self::$broadcast_group_servers;
	}

	/**
	 * @param string $group
	 * @param string $server
	 * @return bool
	 */
	public static function isServerBroadcast(string $group, string $server){
		return isset(self::getBroadcastGroupServers()[$group][$server]);
	}

	/**
	 * @param string $group
	 * @param string $server
	 */
	public static function setBroadcastGroupServers(string $group, string $server): void{
		if (self::isServerBroadcast($group, $server))
			return;
		if (!isset(self::getBroadcastGroupServers()[$group]))
			self::$broadcast_group_servers[$group] = [];
		self::$broadcast_group_servers[$group][$server] = "REGISTERED";
	}

	public static function removeBroadcastServers(string $group, string $server): void{
		if (!self::isServerBroadcast($group, $server))
			return;
		unset(self::$broadcast_group_servers[$group][$server]);
	}

	/**
	 * @return array
	 */
	public static function getGroups(){
		return array_keys(self::getGroupConfig()->getAll());
	}

	public static function getGroupServers(string $group){
		return self::getGroupConfig()->get($group);
	}

	/**
	 * @param string $group
	 * @return bool
	 */
	public static function isGroup(string $group){
		return self::getGroupConfig()->exists($group);
	}

	/**
	 * @param string $group
	 */
	public static function addGroup(string $group){
		self::getGroupConfig()->set($group, []);
		self::getGroupConfig()->save();
	}

	/**
	 * @param string $group
	 */
	public static function removeGroup(string $group){
		if (!self::isGroup($group))
			return;
		self::getGroupConfig()->remove($group);
		self::getGroupConfig()->save();
	}

	/**
	 * @param string $server
	 * @param string $group
	 * @return bool
	 */
	public static function isServerInGroup(string $group, string $server){
		if (!self::isGroup($group))
			return false;
		return in_array($server, self::getGroupServers($group));
	}

	/**
	 * @param string $server
	 * @param string $group
	 */
	public static function addServerToGroup(string $group, string $server){
		if (!self::isGroup($group))
			return;
		if (self::isServerInGroup($group, $server))
			return;
		self::getGroupConfig()->set($group, array_merge(self::getGroupServers($group), [$server]));
		self::getGroupConfig()->save();
		self::loadGroupSigns();
	}

	/**
	 * @param string $server
	 * @param string $group
	 */
	public static function removeServerFromGroup(string $group, string $server){
		if (!self::isGroup($group))
			return;
		if (!self::isServerInGroup($group, $server))
			return;
		$array = self::getGroupServers($group);
		unset($array[array_search($server, $array)]);
		self::getGroupConfig()->set($group, $array);
		self::getGroupConfig()->save();
		self::loadGroupSigns();
	}

	public static function addGroupSign(string $group, Sign $sign){
		self::getGroupSignsConfig()->set(self::stringEncodeVector3($sign->asVector3()), [
			"level"      => $sign->getLevel()->getFolderName(),
			"group_name" => $group,
		])
		;
		self::getGroupSignsConfig()->save();
		self::loadGroupSigns();
	}

	public static function removeGroupSings(Vector3 $vector3){
		self::getGroupSignsConfig()->remove(self::stringEncodeVector3($vector3));
		self::getGroupSignsConfig()->save();
		self::loadGroupSigns();;
	}

	public static function loadGroupSigns()
    {
        self::$group_signs = [];
        self::$broadcast_group_servers = [];
        self::getGroupSignsConfig()->reload();
        $signs = self::getGroupSignsConfig()->getAll();

        foreach (array_keys($signs) as $key) {
            $sign = $signs[$key];
            $vector3 = self::stringDecodeVector3($key);
            $level = $sign["level"];
            $group_name = (string)$sign["group_name"];

            $servers = array_values((array)self::getGroupServers($group_name));

            if (CloudSystem::getInstance()->getServer()->isLevelLoaded($level)) {
                $tile = CloudSystem::getInstance()->getServer()->getLevelByName($level)->getTile($vector3);
                if ($tile instanceof Sign) {
                    self::$group_signs[$key] = new GroupSign($tile, $group_name, $servers);
                    self::$group_signs[$key]->updateSign();
                    self::$group_signs[$key]->reloadSignContent();
                    self::getGroupConfig()->reload();
                }
            }
        }
    }

	/**
	 * @return GroupSign[]
	 */
	public static function getGroupSigns(): array{
		return self::$group_signs;
	}

	public static function stringDecodeVector3(string $string): Vector3{
		$string = explode(":", $string);
		return new Vector3(intval($string[0]), intval($string[1]), intval($string[2]));
	}

	public static function stringEncodeVector3(Vector3 $vector3): string{
		return $vector3->getX() . ":" . $vector3->getY() . ":" . $vector3->getZ();
	}
}