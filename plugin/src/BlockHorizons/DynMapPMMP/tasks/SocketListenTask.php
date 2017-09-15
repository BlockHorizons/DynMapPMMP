<?php

declare(strict_types = 1);

namespace BlockHorizons\DynMapPMMP\tasks;

use BlockHorizons\DynMapPMMP\DynMapPMMP;
use pocketmine\scheduler\PluginTask;

class SocketListenTask extends PluginTask {

	/** @var resource */
	private $socket = null;

	public function __construct(DynMapPMMP $dynMap, $socket) {
		parent::__construct($dynMap);
		if(is_resource($socket)) {
			$this->socket = $socket;
		}
	}

	public function onRun(int $currentTick): void {
		if(($socket = socket_accept($this->socket)) === false) {
			return;
		}
		/** @var DynMapPMMP $owner */
		$owner = $this->getOwner();
		$this->getOwner()->getServer()->getScheduler()->scheduleDelayedTask(new BufferReadTask($owner, $socket), 5);
		$this->getOwner()->tempSocket = $socket;
		$this->getOwner()->getLogger()->debug("Socket received from web page.");
	}
}