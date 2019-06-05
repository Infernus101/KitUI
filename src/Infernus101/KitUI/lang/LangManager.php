<?php

namespace Infernus101\KitUI\lang;

use Infernus101\KitUI\Main;
use pocketmine\utils\Config;

class LangManager {

	const LANG_VERSION = 0;

	private $pl;
	private $defaults;
	private $data;

	public function __construct(Main $pl){
		$this->pl = $pl;
		$this->defaults = [
			"lang-version"     => 0,
			"error-title"      => "Error:",
			"mainmenu-title"   => "Kits -",
			"mainmenu-content" => "Select a kit for info -",
			"select-option"    => "Do you wanna select this kit, player?",
			"selected-kit"     => "Selected kit: {%0}",
			"inv-full"         => "You do not have enough space in your inventory for this kit",
			"cant-afford"      => "You cannot afford kit: {%0} Cost: {%1}",
			"one-per-life"     => "You can only get one kit per life",
			"no-sign-perm"     => "You don't have permission to create kit sign",
			"timer1"           => "Kit {%0} is in cooldown at the moment",
			"timer2"           => "You will be able to get it in {%0}",
			"noperm"           => "You don't have the permission to use kit {%0}",
			"timer-format1"    => "{%0} minutes",
			"timer-format2"    => "{%0} hours and {%1} minutes",
			"timer-format3"    => "{%0} hours",
		];
		$this->data = new Config($this->pl->getDataFolder() . "lang.properties", Config::PROPERTIES, $this->defaults);
		if($this->data->get("lang-version") != self::LANG_VERSION){
			$this->pl->getLogger()->alert("Translation file is outdated. The old file has been renamed and a new one has been created");
			@rename($this->pl->getDataFolder() . "lang.properties", $this->pl->getDataFolder() . "lang.properties.old");
			$this->data = new Config($this->pl->getDataFolder() . "lang.properties", Config::PROPERTIES, $this->defaults);
		}
	}

	public function getTranslation(string $dataKey, ...$args): string{
		if(!isset($this->defaults[$dataKey])){
			$this->pl->getLogger()->error("Invalid datakey $dataKey passed to method LangManager::getTranslation()");

			return "";
		}
		$str = $this->data->get($dataKey, $this->defaults[$dataKey]);
		foreach($args as $key => $arg){
			$str = str_replace("{%" . $key . "}", $arg, $str);
		}

		return $str;
	}

}
