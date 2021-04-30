<?php
namespace pocketmine\cloud\tasks;
use pocketmine\cloud\CloudServer;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Internet;


/**
 * Class AsyncQuery
 * @package pocketmine\cloud\tasks
 * @author xxAROX
 * @date 03.08.2020 - 18:19
 * @project CloudServer
 */
class AsyncQuery extends AsyncTask{
	protected $template;
	/** @var int */
	protected $port;
	/** @var string */
	protected $id;
	/** @var string */
	public $owner;

	/**
	 * AsyncQuery constructor.
	 * @param int $port
	 * @param string $id
	 * @param string $owner
	 * @param string $template
	 */
	public function __construct(int $port, string $id, string $owner, string $template){
		$this->template = $template;
		$this->port = $port;
		$this->id = $id;
		$this->owner = $owner;
	}

	/**
	 * Function onRun
	 * @return void
	 */
	public function onRun(){
		$data = $this->query("127.0.0.1", $this->port, 10);
		$this->setResult($data);

		$server = Server::getInstance();

		$cloud = $server->getCloud();
		$template = $cloud->getTemplateByName($this->template);
		$serv = $template->getServerByID($this->id);

		//CloudServer
		if ($serv instanceof CloudServer) {
			$result = $this->getResult();
			if (!is_null($result["num"]) && ($serv->created + 5) < time()) {
				$serv->setPlayerCount($result["num"]);
			} else {
				if (($serv->created + 10) < time()) {
					$server->getLogger()->info("Server " . $serv->getID() . " stopped.");
					passthru("tmux send -t " . $serv->getID() . " " . "stop" . " ENTER");
					$serv->killScreen();
					$serv->deleteServer();
					$serv->stopServer();
				}
			}
		}

		//PrivateServer
		$privateServer = $template->getServerByOwner($this->owner);
		if ($privateServer instanceof CloudServer) {
			$result = $this->getResult();
			if (!is_null($result["num"]) && ($privateServer->created + 5) < time()) {
				$privateServer->setPlayerCount($result["num"]);
			} else {
				$privateServer->setPlayerCount(0);
				if (($privateServer->created + 20) < time()) {
					$server->getLogger()->info("Server " . $privateServer->getOwner() . " stopped.");
					passthru("tmux send -t " . $privateServer->getOwner() . " " . "stop" . " ENTER");
					$privateServer->killPScreen();
					$privateServer->deletePServer();
					$privateServer->stopPServer();
				}
			}
		}
	}

	/**
	 * Function onCompletion
	 * @param Server $server
	 * @return void
	 */
	public function onCompletion(Server $server): void{
	}

	public static function query(string $host, int $port, int $timeout=10) {

		$socket = @fsockopen("udp://" . $host, $port, $errno, $errstr, $timeout);

		if ($errno || $socket === false) {
			return false;
		}

		stream_Set_Timeout($socket, $timeout);
		stream_Set_Blocking($socket, true);
		$randInt = mt_rand(1, 999999999);
		$reqPacket = "\x01";
		$reqPacket .= pack('Q*', $randInt);
		$reqPacket .= "\x00\xff\xff\x00\xfe\xfe\xfe\xfe\xfd\xfd\xfd\xfd\x12\x34\x56\x78";
		$reqPacket .= pack('Q*', 0);
		fwrite($socket, $reqPacket, strlen($reqPacket));
		$response = fread($socket, 4096);
		fclose($socket);

		if (empty($response) || $response === false) {
			return false;
		}
		if (substr($response, 0, 1) !== "\x1C") {
			return false;
		}
		$serverInfo = substr($response, 35);
		//$serverInfo = preg_replace("#ยง.#", "", $serverInfo);
		$serverInfo = explode(';', $serverInfo);
		return [
			'motd'     => isset($serverInfo[1]) ? $serverInfo[1] : null,
			'version'  => isset($serverInfo[3]) ? $serverInfo[3] : null,
			'num'      => isset($serverInfo[4]) ? $serverInfo[4] : null,
			'max'      => isset($serverInfo[5]) ? $serverInfo[5] : null,
			'id' 	   => "",
			'platform' => "PE"
		];
	}

}
