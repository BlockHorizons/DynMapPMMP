<?php

declare(strict_types = 1);

namespace BlockHorizons\DynMapPMMP;

use BlockHorizons\DynMapPMMP\resources\ConfigurationHandler;
use BlockHorizons\DynMapPMMP\tasks\ChunkFetchTask;
use BlockHorizons\DynMapPMMP\tasks\SocketListenTask;
use pocketmine\plugin\PluginBase;

class DynMapPMMP extends PluginBase {

	/** @var null|resource */
	private $socket = null;
	/** @var ConfigurationHandler */
	private $configHandler = null;

	public function onEnable() {
		$this->configHandler = new ConfigurationHandler($this);
		$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		socket_bind($this->socket, "127.0.0.1", 80);
		socket_listen($this->socket, 5);
		socket_set_nonblock($this->socket);

		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		socket_connect($socket, "127.0.0.1", 80);
		socket_write($socket, "This is the buffer", strlen("This is the buffer"));

		$this->getServer()->getScheduler()->scheduleRepeatingTask(new SocketListenTask($this, $this->socket), 5);

		$this->mapAllWorlds();
	}

	public function onDisable(): void {

	}

	public function mapAllWorlds() {
		// Note! This is for testing only, and should not be used for more than 5 big regions at once.
		$this->getServer()->getScheduler()->scheduleAsyncTask(new ChunkFetchTask($this->getServer()->getDataPath()));
	}
}