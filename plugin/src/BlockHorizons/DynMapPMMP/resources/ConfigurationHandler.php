<?php

declare(strict_types = 1);

namespace BlockHorizons\DynMapPMMP\resources;

use BlockHorizons\DynMapPMMP\DynMapPMMP;

class ConfigurationHandler {

	/** @var DynMapPMMP */
	private $dynMap = null;
	/** @var array */
	private $data = [];

	public function __construct(DynMapPMMP $dynMap) {
		$dynMap->saveDefaultConfig();
		$this->dynMap = $dynMap;
		$this->data = yaml_parse_file($dynMap->getDataFolder() . "config.yml");
	}

	/**
	 * @return DynMapPMMP
	 */
	public function getDynMap(): DynMapPMMP {
		return $this->dynMap;
	}

	/**
	 * @return int
	 */
	public function getDynMapPort(): int {
		return (int) $this->data["DynMap-Port"];
	}
}