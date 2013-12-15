<?php
require_once 'WhatsAppEventHandler.php';

/**
 * @file
 * Event class to fire WhatsApp related events.
 */

/**
 *
 */
class WhatsAppEvent
{
    static $event_callbacks;

    /**
     * Constructor.
     *
     * @param string $event
     *   Name of the event (optional).
     */
    function __construct($event = null)
    {
        // Register the event if any.
        if (!empty($event)) {
            $this->register($event);
        }
    }

    /**
     * Registers an event.
     *
     * @param string $event
     *   Name of the event.
     */
    protected function register($event)
    {
        if (empty(self::$event_callbacks[$event])) {
            self::$event_callbacks[$event] = array();
        }
    }

    /**
     * bindAll
     * 
     * Binds all the whatsapp events.
     *
     * @param a WhatsAppEventHandler
    */
    function bindAll(WhatsAppEventHandler $wh)
    {
        $events = array(
            "onClose",
            "onCodeRegister",
            "onCodeRegisterFailed",
            "onCodeRequest",
            "onCodeRequestFailed",
            "onCodeRequestFailedTooRecent",
            "onConnect",
            "onCredentialsBad",
            "onCredentialsGood",
            "onDisconnect",
            "onDissectPhone",
            "onDissectPhoneFailed",
            "onGetAudio",
            "onGetError",
            "onGetGroups",
            "onGetGroupsInfo",
            "onGetGroupsSubject",
            "onGetImage",
            "onGetLocation",
            "onGetMessage",
            "onGetGroupMessage",
            "onGetPrivacyBlockedList",
            "onGetProfilePicture",
            "onGetRequestLastSeen",
            "onGetServerProperties",
            "onGetvCard",
            "onGetVideo",
            "onGroupsChatCreate",
            "onGroupsChatEnd",
            "onGroupsParticipantsAdd",
            "onGroupsParticipantsRemove",
            "onLogin",
            "onMessageComposing",
            "onMessagePaused",
            "onMessageReceivedClient",
            "onMessageReceivedServer",
            "onPing",
            "onPresence",
            "onSendMessageReceived",
            "onSendPong",
            "onSendPresence",
            "onSendStatusUpdate",
            "onUploadFile",
            "onUploadFileFailed"
        );

        foreach( $events as $event ) {
            if( method_exists($wh,$event) ) {
                $this->bind($event,array($wh, $event));
            } else {
                // This should never happen:
                throw new Exception("Cannot find needed method: " . $event );
            }
        }
    }
    
    /**
     * Binds a callback to a event.
     *
     * @param string $event
     *   Name of the event.
     * @param string $callback
     *   The method or function to call.
     */
    public function bind($event, $callback)
    {
        self::$event_callbacks[$event][] = $callback;
    }

    /**
     * Executes all the binded callbacks when the event is fired.
     *
     * @param string $event
     *   Name of the event.
     * @param array $arguments
     *   The arguments to pass to each callback.
     */
    public function fire($event, $arguments = array())
    {
        if (!empty(self::$event_callbacks[$event])) {
            foreach (self::$event_callbacks[$event] as $callback) {
                call_user_func_array($callback, $arguments);
            }
        }
    }

}
