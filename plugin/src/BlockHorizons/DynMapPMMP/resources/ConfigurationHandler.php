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
		if(!$this->checkAPIKey()) {
			$dynMap->getLogger()->error("API Key \"" . $this->getAPIKey() . "\" is invalid!");
			//$dynMap->getServer()->getPluginManager()->disablePlugin($dynMap);
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
		if(empty($this->data["API-Key"])) {
			return false;
		}
		$serverCredentials = explode(":", base64_decode($this->data["API-Key"]));
		if(strpos($serverCredentials[0], ".") === false) {
			return false;
		}
		if(!is_numeric($serverCredentials[1])) {
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