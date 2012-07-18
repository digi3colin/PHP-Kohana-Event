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

classes/ieventdispatcher.php
IEventDispatcher is interface of eventDispatcher.

classes/eventdispatcher.php
the EventDispatcher follow W3C event model.

classes/id3eventdispatcher.php
ID3EventDispatcher add once and when method

classes/d3eventdispatcher.php
D3EventDispatcher is base classes of event dispatcher with once and when method.

classes/event.php
Event is the w3c event object.

classes/errorevent.php
ErrorEvent is the event object with text and id for error report