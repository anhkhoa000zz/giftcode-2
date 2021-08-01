<?
namespace giftcode\Anhkhoaaa\Task;

use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\Player;
use giftcode\Anhkhoaaa\Main;

class giftcodeTask extends Task {
	public $plugin;
	public $int;	

	public function __construct(Main $plugin, int $int){
		$this->plugin = $plugin;
		$this->int = $int;
	}

	public function onRun($tick){
		foreach($this->plugin->getServer()->getOnlinePlayers() as $p){
			if($this->plugin->config->get($p->getName()) == true){
				$code = $this->plugin->code->getAll();
				foreach ($code as $name) {
					if($this->int == time()){
						$inName = $name["name"];
						$save = $this->plugin->code->get($inName);
						if($save["time"] == "yes"){
							$this->plugin->code->remove($save["name"]);
							$this->plugin->code->save();
							$status = unlink($this->plugin->getDataFolder()."Codes/".$save["name"].".yml");
							$this->plugin->config->remove($p->getName());
							$this->plugin->config->save();
							$p->sendMessage("§aCode §c".$save["name"]."§a đã không còn hiệu lực.");
						}
					}
				}
			}
		}
	}

}