<?php
/**
 * digi3colin | Date: 12年7月18日 | Time: 上午10:38
 */
class D3EventDispatcher extends EventDispatcher implements ID3EventDispatcher{
	private $once_callbacks;
	private $once_callback;

	public function __construct(){
		parent::__construct();

		$this->once_callbacks=array();
		$this->once_callback=array($this,'run_once');
	}

	public function when($eventType,$callback){
		$this->addEventListener($eventType,$callback,false,0);
		return $this;
	}

	public function once($eventType,$callback){
		//if event dispatched, it will remove after dispatched.
		if(empty($this->once_callbacks[$eventType])){
			$this->once_callbacks[$eventType] = array();//this array<function> cannot release until gc;

			$this->addEventListener($eventType, $this->once_callback, false, 0);
		}

		$this->once_callbacks[$eventType][]=$callback;

		return $this;
	}

	public function run_once(Event &$event){
		$this->removeEventListener($event->type, $this->once_callback);
		//the callback may call once() again
		//then the dict[e.type] will increase;
		//prevent this by clone the array;
		$callbacks = &$this->once_callbacks[$event->type];
		$count = count($callbacks);
		for($i=0;$i<$count;$i++){
			call_user_func($callbacks[$i],$event);
			if($event->isEventStopped)break;
		}
		unset($this->once_callbacks[$event->type]);
	}
}
