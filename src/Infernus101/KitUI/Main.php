<?php

namespace Infernus101\KitUI;

use Infernus101\KitUI\UI\Handler;
use Infernus101\KitUI\lang\LangManager;
use Infernus101\KitUI\tasks\CoolDownTask;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class Main extends PluginBase implements Listener {
	
  public $kits = [];
  public $kitused = [];
  public $language;
	
    public function onEnable(){
      @mkdir($this->getDataFolder()."timer/");
      $this->getServer()->getLogger()->notice("[KitUI] Enabled! - By Infernus101");
      $this->configFixer();
      $files = array("kits.yml","config.yml");
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
      $this->getServer()->getScheduler()->scheduleDelayedRepeatingTask(new CoolDownTask($this), 1200, 1200);
      $allKits = yaml_parse_file($this->getDataFolder()."kits.yml");
      foreach($allKits as $name => $data){
        $this->kits[$name] = new Kit($this, $data, $name);
      }
    }
	
    public function onDisable(){
      foreach($this->kits as $kit){
        $kit->save();
      }
      $this->getServer()->getLogger()->notice("[KitUI] Disabled! - By Infernus101");
    }
	
	public function onCommand(CommandSender $sender, Command $cmd, String $label, array $args): bool{
	  if(!$sender instanceof Player){
		  $sender->sendMessage(TextFormat::RED."> Command must be run ingame!");
		  return true;
	  }
	  switch(strtolower($cmd->getName())){
            case "kit":
			if(!$sender->hasPermission("kit.command")){
				$sender->sendMessage(TextFormat::RED."> You don't have permission to use this command!");
				return false;
			}
			if(isset($args[0])){
				$sender->sendMessage(TextFormat::GREEN."About:\nKit UI by Infernus101! github.com/Infernus101/KitUI\n".TextFormat::AQUA."Servers - FallenTech.tk | CounterTech.tk 19132");
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
	
    private function configFixer(){
          $this->saveResource("kits.yml");
          $allKits = yaml_parse_file($this->getDataFolder()."kits.yml");
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

    public function getPlayerKit($player, $obj = false){
          if($player instanceof Player){
        $player = $player->getName();
      }
          return isset($this->kitused[strtolower($player)]) ? ($obj ? $this->kitused[strtolower($player)] : $this->kitused[strtolower($player)]->getName()) : null;
      }

      public function getKit(string $kit){
          $lower = array_change_key_case($this->kits, CASE_LOWER);
          if(isset($lower[strtolower($kit)])){
              return $lower[strtolower($kit)];
          }
          return null;
      }
 }
