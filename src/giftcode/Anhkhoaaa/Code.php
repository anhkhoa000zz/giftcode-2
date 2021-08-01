<?
namespace giftcode\Anhkhoaaa;

use giftcode\Anhkhoaaa\Main;
use onebone\economyapi\EconomyAPI;
use onebone\pointapi\PointAPI;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;

class Code {
	const PREFIX = "§6§6[§6GIFTCODE§6]: §a";
	public $plugin;
	public function __construct(Main $plugin)
	{$this->plugin = $plugin;}

	public function getCode($code){
		$name = $this->plugin->code->get($code)["name"];
		$money = $this->plugin->code->get($code)["money"];
		$time = $this->plugin->code->get($code)["time"];
		$point = $this->plugin->code->get($code)["point"];
		$command = $this->plugin->code->get($code)["command"];
		$item = $this->plugin->code->get($code)["item"];
		$all = $this->plugin->code->get($code)["count"];
		return [$name, $money, $time, $point, $command, $all, $item];
	}

	public function setCode($name, $money, $time , $point, $command, $all, $item){
		$this->plugin->code->set($name, ["name" => $name, "money" => $money,"time" => $time, "point" => $point, "command" => $command,"count" => $all, "item" => $item]);
		$this->plugin->code->save();
	}

	public function giveGift($p, $name, $money, $point, $command){
		$cm = str_replace(["{player}", "{pl}"], [$p->getName(), ''], $command);
		EconomyAPI::getInstance()->addMoney($p, $money);  
		PointAPI::getInstance()->addPoint($p, $point); 
		$this->plugin->getServer()->getCommandMap()->dispatch($p, $cm);
		$p->sendMessage(self::PREFIX."Bạn đã sử dụng code §c".$name."§a thành công.");
		$p->sendMessage(self::PREFIX."+ §c".$money."§a Money!");
		$p->sendMessage(self::PREFIX."+ §c".$point."§a Point!");
	}
}