<?php

declare(strict_types = 1);

namespace BlockHorizons\DynMapPMMP;

use BlockHorizons\DynMapPMMP\resources\ConfigurationHandler;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginException;

class DynMapPMMP extends PluginBase {

	/** @var null|resource */
	private $socket = null;
	/** @var ConfigurationHandler */
	private $configHandler = null;

	public function onEnable(): void {
		$this->configHandler = new ConfigurationHandler($this);

		$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		$connectionResult = socket_bind($this->socket, '127.0.0.1', 1000); // TODO: Change when we have someone hosting.
		if($connectionResult === false) {
			$this->getServer()->getPluginManager()->disablePlugin($this);
			throw new PluginException("Unable to connect to DynMap Website.");
		}
		socket_listen($this->socket, 5);
		socket_set_nonblock($this->socket);
	}

	public function onDisable(): void {

	}
}