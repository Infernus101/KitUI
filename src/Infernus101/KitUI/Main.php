<?php

namespace Infernus101\KitUI;

use Infernus101\KitUI\lang\LangManager;
use Infernus101\KitUI\tasks\CoolDownTask;
use Infernus101\KitUI\UI\Handler;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class Main extends PluginBase implements Listener {

	/** @var Kit[] */
	public $kits = [];
	/** @var Kit[] */
	public $kitUsed = [];
	/** @var LangManager */
	public $language;
	/** @var \PiggyCustomEnchants\Main */
	public $piggyEnchants;
	/** @var Config */
	public $config;
	/** @var string[][] */
	public $formData;
	/** @var Config */
	private $kit;

	public function onEnable(){
		@mkdir($this->getDataFolder() . "timer/");
		$this->configFixer();
		$files = ["kits.yml", "config.yml"];
		foreach($files as $file){
			if(!file_exists($this->getDataFolder() . $file)){
				@mkdir($this->getDataFolder());
				file_put_contents($this->getDataFolder() . $file, $this->getResource($file));
			}
		}
		$this->kit = new Config($this->getDataFolder() . "kits.yml", Config::YAML);
		$this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
		$this->language = new LangManager($this);
		$this->getServer()->getPluginManager()->registerEvents(new PlayerEvents($this), $this);
		$this->getScheduler()->scheduleDelayedRepeatingTask(new CoolDownTask($this), 1200, 1200);
		$this->piggyEnchants = $this->getServer()->getPluginManager()->getPlugin("PiggyCustomEnchants");
		$allKits = yaml_parse_file($this->getDataFolder() . "kits.yml");
		foreach($allKits as $name => $data){
			$this->kits[$name] = new Kit($this, $data, $name);
		}
	}

	private function configFixer(){
		$this->saveResource("kits.yml");
		$allKits = yaml_parse_file($this->getDataFolder() . "kits.yml");
		$this->fixConfig($allKits);
		foreach($allKits as $name => $data){
			$this->kits[$name] = new Kit($this, $data, $name);
		}
	}

	private function fixConfig(&$config){
		foreach($config as $name => $kit){
			if(isset($kit["users"])){
				$users = array_map("strtolower", $kit["users"]);
				$config[$name]["users"] = $users;
			}
			if(isset($kit["worlds"])){
				$worlds = array_map("strtolower", $kit["worlds"]);
				$config[$name]["worlds"] = $worlds;
			}
		}
	}

	public function onDisable(){
		foreach($this->kits as $kit){
			$kit->save();
		}
	}

	public function onCommand(CommandSender $sender, Command $cmd, String $label, array $args): bool{
		if(!$sender instanceof Player){
			$sender->sendMessage(TextFormat::RED . "> Command must be run ingame!");

			return true;
		}
		switch(strtolower($cmd->getName())){
			case "kit":
				if(!$sender->hasPermission("kit.command")){
					$sender->sendMessage(TextFormat::RED . "> You don't have permission to use this command!");

					return false;
				}
				if(isset($args[0])){
					$sender->sendMessage(TextFormat::GREEN . "About:\nKit UI by Infernus101! github.com/Infernus101/KitUI\n" . TextFormat::BLUE . "Servers - FallenTech.tk 19132");

					return false;
				}
				$handler = new Handler();
				$packet = new ModalFormRequestPacket();
				$packet->formId = $handler->getWindowIdFor(Handler::KIT_MAIN_MENU);
				$packet->formData = $handler->getWindowJson(Handler::KIT_MAIN_MENU, $this, $sender);
				$sender->dataPacket($packet);
				break;
		}

		return true;
	}

	public function getPlayerKit($player, $obj = false){
		if($player instanceof Player){
			$player = $player->getName();
		}

		return isset($this->kitUsed[strtolower($player)]) ? ($obj ? $this->kitUsed[strtolower($player)] : $this->kitUsed[strtolower($player)]->getName()) : null;
	}

	public function getKit(string $kit): ?Kit{
		$lower = array_change_key_case($this->kits, CASE_LOWER);
		if(isset($lower[strtolower($kit)])){
			return $lower[strtolower($kit)];
		}

		return null;
	}
}
