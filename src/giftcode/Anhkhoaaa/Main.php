<?
namespace giftcode\Anhkhoaaa;

use pocketmine\{Server, Player, IPlayer};
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\{Command, CommandSender};
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\item\Item;
use giftcode\Anhkhoaaa\Task\giftcodeTask;
use giftcode\Anhkhoaaa\ItemData;
use giftcode\Anhkhoaaa\Code;
use giftcode\Anhkhoaaa\Form;

class Main extends PluginBase implements Listener{

	public static $khoiDongAddItem;
	public static $nameCode;
	public static $arr = [];
	const PREFIX = "§6§6[§6GIFTCODE§6]: §a";
    #Task	
    public function initTask(){
    	$this->getScheduler()->scheduleRepeatingTask(new Task\giftcodeTask($this, time()+$this->config->get("thời gian")*60), 20);
    }
	public function onEnable(){
		$this->saveResource("config.yml");
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$this->code = new Config($this->getDataFolder(). "giftcode.yml", Config::YAML);
		$this->config = new Config($this->getDataFolder(). "config.yml", Config::YAML);
       	@mkdir($this->getDataFolder()."Codes/");
       	$this->saveDefaultConfig();
	}
	public function onCommand(CommandSender $p, Command $cmd, string $str, array $args) : bool{
		$form = new Form($this);
		switch ($cmd->getName()) {
			case 'giftcode':
			if(!isset($args[0])){
				$form->claimGiftcode($p);
				if($p->hasPermission("op")){
					$p->sendMessage("Hãy sử dụng: /gc §c[create] §f| §c[list] §f| §c[delete]");
				} 
				return false;
			}
			switch(strtolower($args[0])){
				case "create":
				$p->hasPermission("op") == true ? $form->create($p) : $p->sendMessage("!");
				break;
				case "additem":
				if($p->hasPermission("op")){
					if(!isset($args[1])){
						$p->sendMessage("Vui lòng nhập tên của code");
						return false;
					}
					if(!$this->code->exists($args[1])){
						$p->sendMessage("Code này không tồn tại!");
						return false;
					}
					$this->addItem($p, $args[1]);
					return false;
				}
				$p->sendMessage("!");
				break;
				case "list":
				if($p->hasPermission("op")){
					$i = 0;
					$all = $this->code->getAll();
					if($all == null){
						$p->sendMessage(self::PREFIX."Không có code có sẵn nào!");
						return false;
					}
					foreach($all as $names){
						foreach($names as $name=>$val){
							if($name != "name"){
								continue;
							}
							$i++;
							$vcl = $this->code->get($val)["count"];
							$time = $this->code->get($val)["time"];
							$time == "yes" ? $t = "§7Code §c5 §7phút." : $t = "";
							if($vcl === "full"){
								$kT = "Giftcode §cChung§7. Mỗi người được 1 lần dùng. ";
							}
							elseif($vcl == 1){
								$kT = "Giftcode §c1 §7lần dùng. Có thể dùng. ";
							}
							elseif($vcl == 0){
								$kT = "Giftcode §cđã §7dùng rồi. ";
							} else {
								$kT = "";
							}
							$p->sendMessage(" §7". $i .">§d ". $val. "§7: ".$kT.$t);
							
						}
					}
					return false;
				}
				$p->sendMessage("!");
				break;
				case "delete":
				if(!isset($args[1])){
						$p->sendMessage("Vui lòng nhập tên của code");
						return false;
					}
					if($this->code->exists($args[1])){
						$this->code->remove($args[1]);
						$this->code->save();
						$p->sendMessage(self::PREFIX."Đã xóa thành công!");
						return false;
					}
					$p->sendMessage(self::PREFIX."Code này không tồn tại.");
				break;
			}
			break;
		}
		return true;
	}
	public function onChat(PlayerChatEvent $ev){
		$p = $ev->getPlayer();
		$inv = $p->getInventory();
		$playerChat = $ev->getMessage();
		if(self::$khoiDongAddItem == true){
			if($playerChat == "sure"){
				$item = $inv->getItemInHand();
				$editItem = new ItemData($this);
				$saveItem = $editItem->itemToData($item);
				self::$arr[] = $saveItem;
				$p->sendMessage(self::PREFIX." đã §cAddItem§a thành công! Có thể tắt khi nhấn §c'off'");
				$ev->setCancelled();
			}
			if($playerChat == "off"){
				$duLieu = new Code($this);
				$data = $duLieu->getCode(self::$nameCode);
				$money = $data[1];$time = $data[2];$point = $data[3];$command = $data[4];$all = $data[5];
				$duLieu->setCode(self::$nameCode, $money, $time, $point, $command, $all, self::$arr);
				self::$khoiDongAddItem = false;
				self::$nameCode = "";
				self::$arr = [];
				$p->sendMessage(self::PREFIX."§c Off§a chức năng AddItem thành công!");
				$ev->setCancelled();
			}
		}
	}
	public function addItem(Player $p, $code){
		$p->sendMessage(self::PREFIX."Vui lòng cầm item muốn add trên tay. Khi đã chắc chắn hãy gõ §c'sure' §alệnh trên thanh chat.");
		self::$khoiDongAddItem = true;
		self::$nameCode = $code;
	}
}