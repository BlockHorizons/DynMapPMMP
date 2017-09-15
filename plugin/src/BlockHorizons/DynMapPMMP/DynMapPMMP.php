<?php

declare(strict_types = 1);

namespace BlockHorizons\DynMapPMMP;

use BlockHorizons\DynMapPMMP\resources\ConfigurationHandler;
use BlockHorizons\DynMapPMMP\tasks\ImageRegionFetchThread;
use BlockHorizons\DynMapPMMP\tasks\SocketListenTask;
use pocketmine\level\Level;
use pocketmine\plugin\PluginBase;

class DynMapPMMP extends PluginBase {

	/** @var null|resource */
	private $socket = null;
	/** @var ConfigurationHandler */
	private $configHandler = null;
	/** @var null|resource */
	public $tempSocket = null;

	public function onEnable(): void {
		$this->configHandler = new ConfigurationHandler($this);
		$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		socket_bind($this->socket, "127.0.0.1", $this->configHandler->getDynMapPort());
		socket_listen($this->socket, 5);
		socket_set_nonblock($this->socket);
		$this->getLogger()->info("Socket listening for connections...");

		$this->getServer()->getScheduler()->scheduleRepeatingTask(new SocketListenTask($this, $this->socket), 5);
	}

	public function onDisable(): void {

	}

	/**
	 * @param int        $regionX
	 * @param int        $regionZ
	 * @param Level|null $level
	 */
	public function requestRegion(int $regionX, int $regionZ, Level $level = null): void {
		if($level === null) {
			$level = $this->getServer()->getDefaultLevel();
		}
		$this->getServer()->getScheduler()->scheduleAsyncTask(new ImageRegionFetchThread($this->getServer()->getDataPath(), $regionX, $regionZ, $level));
	}

	/**
	 * @param string $image
	 */
	public function submitImage(string $image): void {
		socket_write($this->tempSocket, "REGION_RESPONSE" . $image, strlen("REGION_RESPONSE" . $image));
		$this->tempSocket = null;
		$this->getLogger()->debug("Socket sent to web page.");
	}
}