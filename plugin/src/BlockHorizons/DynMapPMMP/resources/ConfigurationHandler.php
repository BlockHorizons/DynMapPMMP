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
		$this->dynMap = $dynMap;
		$this->data = yaml_parse_file($dynMap->getDataFolder() . "config.yml");
		if(!$this->checkAPIKey()) {
			$dynMap->getLogger()->error("API Key \'" . $this->getAPIKey() . "\' is invalid!");
			$dynMap->getServer()->getPluginManager()->disablePlugin($dynMap);
		}
	}

	/**
	 * @return DynMapPMMP
	 */
	public function getDynMap(): DynMapPMMP {
		return $this->dynMap;
	}

	/**
	 * @return bool
	 */
	public function checkAPIKey(): bool {
		$serverCredentials = explode(":", base64_decode($this->data["API-Key"]));
		$address = $serverCredentials[0];
		if(strpos($address, ".") === false) {
			return false;
		}
		$port = $serverCredentials[1];
		if(!is_numeric($port)) {
			return false;
		}
		return true;
	}

	/**
	 * @return string
	 */
	public function getAPIKey(): string {
		return $this->data["API-Key"];
	}
}