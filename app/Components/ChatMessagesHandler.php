<?php

namespace App\Components;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use App\Entities\Room;

class ChatMessagesHandler implements MessageComponentInterface
{

    const MSG_TYPE_ROOM_CONNECT = 'room_connect';
    const MSG_TYPE_MESSAGE_ADD  = 'message_add';

    const MSG_CLIENT_TYPE_ROOM_CONNECTED   = 'room_connected';
    const MSG_CLIENT_TYPE_ROOM_REJECTED    = 'room_rejected';
    const MSG_CLIENT_TYPE_MESSAGE_ADDED    = 'message_added';
    const MSG_CLIENT_TYPE_MESSAGE_REJECTED = 'message_rejected';
    const MSG_CLIENT_TYPE_MESSAGE_NEW      = 'message_new';

    const MESSENGER_TYPE_INFO  = 'info';
    const MESSENGER_TYPE_ERROR = 'error';

    /**
     * @var array
     */
    public static $availableMsgTypes = [
        self::MSG_TYPE_ROOM_CONNECT,
        self::MSG_TYPE_MESSAGE_ADD
    ];

    /**
     * @var \SplObjectStorage
     */
    private $clients;

    /**
     * @var array
     */
    private $messengers = [
        self::MESSENGER_TYPE_INFO  => null,
        self::MESSENGER_TYPE_ERROR => null
    ];

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
    }

    /**
     * @param string   $type
     * @param \Closure|array $handler
     *
     * @throws \Exception
     */
    public function setMessenger($type, $handler)
    {
        if (!array_key_exists($type, $this->messengers)) {
            throw new \Exception('Invalid messenger type.');
        }
        if (!($handler instanceof \Closure) && (!is_array($handler) || count($handler) != 2)) {
            throw new \Exception('Invalid message handler.');
        }

        $this->messengers[$type] = $handler;
    }

    /**
     * @param string $message
     */
    private function printInfo($message)
    {
        if ($handler = $this->messengers[self::MESSENGER_TYPE_INFO]) {
            if ($handler instanceof \Closure)
                $handler($message);
            else
                call_user_func($handler, $message);
        }
    }

    /**
     * @param string $error
     */
    private function printError($error)
    {
        if ($handler = $this->messengers[self::MESSENGER_TYPE_ERROR]) {
            if ($handler instanceof \Closure)
                $handler($error);
            else
                call_user_func($handler, $error);
        }
    }

    /**
     * @param ConnectionInterface $conn
     */
    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);

        $this->printInfo("New client #$conn->resourceId has connected.");
    }

    /**
     * @param ConnectionInterface $from
     * @param string              $message
     */
    public function onMessage(ConnectionInterface $from, $message)
    {
        list($msgType, $msgBody) = $this->parseMessage($message);
        $this->processMessage($from, $msgType, $msgBody);
    }

    /**
     * @param ConnectionInterface $conn
     */
    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);

        $this->printInfo("Client #$conn->resourceId has disconnected.");
    }

    /**
     * @param ConnectionInterface $conn
     * @param \Exception          $e
     */
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $conn->close();

        $this->printError("Client #$conn->resourceId error: " . $e->getMessage());
    }

    /**
     * @param $message
     *
     * @return array
     */
    private function parseMessage($message)
    {
        $parsed = json_decode($message);

        if (!is_array($parsed) ||
            count($parsed) !== 2 ||
            !in_array($parsed[0], self::$availableMsgTypes) ||
            gettype($parsed[1]) !== 'string'
        ) {
            return [null, $message];
        }

        return $parsed;
    }

    /**
     * @param ConnectionInterface $conn
     * @param string              $clientType
     * @param string              $data
     */
    private function sendClientMessage(ConnectionInterface $conn, $clientType, $data = null)
    {
        $message = [$clientType];
        if (!is_null($data)) {
            $message[] = $data;
        }

        $conn->send(json_encode($message));
    }

    /**
     * @param ConnectionInterface $conn
     * @param string              $type
     * @param mixed               $message
     */
    private function processMessage(ConnectionInterface $conn, $type, $message)
    {
        switch ($type) {
            case self::MSG_TYPE_ROOM_CONNECT:
                if ($this->connectToRoom($conn, intval($message))) {
                    $this->sendClientMessage($conn, self::MSG_CLIENT_TYPE_ROOM_CONNECTED);
                }
                else {
                    $this->sendClientMessage($conn, self::MSG_CLIENT_TYPE_ROOM_REJECTED);
                }
                break;

            case self::MSG_TYPE_MESSAGE_ADD:
                if ($this->addMessage($conn, $message)) {
                    $this->sendClientMessage($conn, self::MSG_CLIENT_TYPE_MESSAGE_ADDED);
                }
                else {
                    $this->sendClientMessage($conn, self::MSG_CLIENT_TYPE_MESSAGE_REJECTED);
                }
                break;

            default:
                $this->printError("Client #$conn->resourceId incorrect message: " . $message);
        }
    }

    /**
     * @param ConnectionInterface $conn
     * @param integer             $roomId
     *
     * @return bool
     */
    private function connectToRoom(ConnectionInterface $conn, $roomId)
    {
        if (!Room::find($roomId)) {
            return false;
        }

        $this->clients->attach($conn, $roomId);
        $this->printInfo("Client #$conn->resourceId listen to room #$roomId now.");

        return true;
    }

    /**
     * @param ConnectionInterface $conn
     * @param string              $message
     */
    private function addMessage(ConnectionInterface $conn, $message)
    {
        if (!$this->clients->offsetExists($conn)) {
            return false;
        }

        $roomId = $this->clients->offsetGet($conn);

        $this->clients->rewind();
        /**
         * @var ConnectionInterface $itemConn
         */
        while ($this->clients->valid()) {
            $itemConn = $this->clients->current();
            $itemRoomId = $this->clients->offsetGet($itemConn);

            if ($itemRoomId == $roomId/* && $itemConn != $conn*/) {
                $this->sendClientMessage($itemConn, self::MSG_CLIENT_TYPE_MESSAGE_NEW, $message);
            }
            $this->clients->next();
        }

        $this->printInfo("Client #$conn->resourceId has posted message: " . (strlen($message) > 50 ? substr($message, 0, 50).'...' : $message));

        return true;
    }
}