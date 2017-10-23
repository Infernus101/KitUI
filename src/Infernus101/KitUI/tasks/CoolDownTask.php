<?php

namespace Infernus101\KitUI\tasks;

use Infernus101\KitUI\Main;
use pocketmine\scheduler\PluginTask;

class CoolDownTask extends PluginTask{

    private $plugin;

    public function __construct(Main $plugin){
        parent::__construct($plugin);
        $this->plugin = $plugin;
    }

    public function onRun(int $tick){
        foreach($this->plugin->kits as $kit){
            $kit->processTimer();
        }
    }

}
