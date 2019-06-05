<?php

namespace Infernus101\KitUI\tasks;

use Infernus101\KitUI\Main;
use pocketmine\scheduler\Task;

class CoolDownTask extends Task {

	private $plugin;

	public function __construct(Main $plugin){
		$this->plugin = $plugin;
	}

	public function onRun(int $tick){
		foreach($this->plugin->kits as $kit){
			$kit->processTimer();
		}
	}

}
