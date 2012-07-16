PHP-Kohana-Event
================

PHP-Kohana-Event follows the format of w3c DOM event model.
* http://www.w3.org/TR/DOM-Level-2-Events/events.html
* current version not implement the bubbling of event
* add when(EventName,callback) and once(EventName,callback) method to listen event with a shorten method name.

To use it:
$evt = new EventDispatcher();
$evt.addEventListener(new Event('change',array($this,'onChange'));
$evt.when(new Event('loading',array($this,'onProgress'));
$evt.once(new Event('loaded',array($this,'onLoad'));

function onChange($e){
	print($e->target);
}

function onProgress($e){
	print($e->target);
}

function onLoad($e){
	print($e->type);
}