<?php

namespace Infernus101\KitUI\events;

use Infernus101\KitUI\Kit;
use pocketmine\event\Event;

/**
 * The backbone of the kit event itself
 *
 * @package Infernus101\KitUI\events
 * @author larryTheCoder
 */
abstract class KitEvent extends Event {

	/** @var Kit */
	private $kit;

	public function __construct(Kit $kit){
		$this->kit = $kit;
	}

	/**
	 * Get the kit that is being used.
	 *
	 * @return Kit
	 */
	public function getKit(){
		return $this->kit;
	}
}