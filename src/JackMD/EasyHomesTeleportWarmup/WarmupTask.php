<?php
declare(strict_types = 1);

namespace JackMD\EasyHomesTeleportWarmup;

use pocketmine\entity\Effect;
use pocketmine\level\sound\EndermanTeleportSound;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use function is_null;

class WarmupTask extends Task{

	/** @var EasyHomesTeleportWarmup */
	private $plugin;

	public function __construct(EasyHomesTeleportWarmup $plugin){
		$this->plugin = $plugin;
	}

	public function onRun(int $currentTick){
		foreach($this->plugin->getWarmups() as $playerName => $warmupData){
			/** @var Player $player */
			$player = $warmupData[0];
			$homeLocation = $warmupData[1];

			if(is_null($time = $this->plugin->reduceTime($player))){
				$this->plugin->removeWarmup($player);
				continue;
			}

			if($time > 0){
				continue;
			}

			if($player->hasEffect(Effect::NAUSEA)){
				$player->removeEffect(Effect::NAUSEA);
			}

			// broadcast sound at both positions
			$player->getLevel()->addSound(new EndermanTeleportSound($player->asVector3()));
			$player->teleport($homeLocation);
			$player->getLevel()->addSound(new EndermanTeleportSound($player->asVector3()));
			$player->sendMessage($this->plugin->getConfig()->getNested("tp-success", "Successfully teleported you to your home."));

			$this->plugin->removeWarmup($player);
		}
	}
}