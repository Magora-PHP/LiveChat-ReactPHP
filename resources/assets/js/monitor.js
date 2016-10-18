var Monitor = function()
{
    const MSG_SERVER_TYPE_ROOM_CONNECT = 'room_connect';
    const MSG_SERVER_TYPE_MESSAGE_ADD  = 'message_add';

    const MSG_TYPE_ROOM_CONNECTED   = 'room_connected';
    const MSG_TYPE_ROOM_REJECTED    = 'room_rejected';
    const MSG_TYPE_MESSAGE_ADDED    = 'message_added';
    const MSG_TYPE_MESSAGE_REJECTED = 'message_rejected';
    const MSG_TYPE_MESSAGE_NEW      = 'message_new';

    var availableMsgTypes = [
        MSG_TYPE_ROOM_CONNECTED,
        MSG_TYPE_ROOM_REJECTED,
        MSG_TYPE_MESSAGE_ADDED,
        MSG_TYPE_MESSAGE_REJECTED,
        MSG_TYPE_MESSAGE_NEW
    ];

    var self = this,
        params = {
            connection: null,
            roomId: null,
            roomConnected: false,
            onConnected: function() {},
            messageListeners: []
        };

    var parseMessage = function(message) {
        var parsed = JSON.parse(message);

        if (!(parsed instanceof Array) || !parsed.length || availableMsgTypes.indexOf(parsed[0]) < 0) {
            return [null, message];
        }

        return parsed;
    };
    var processMessage = function(type, message) {
        switch (type) {
            case MSG_TYPE_ROOM_CONNECTED:
                params.roomConnected = true;
                params.onConnected();
                break;

            case MSG_TYPE_ROOM_REJECTED:
                console.error('Room connection problem.');
                // @todo try to reconnect
                break;

            case MSG_TYPE_MESSAGE_ADDED:
                break;

            case MSG_TYPE_MESSAGE_REJECTED:
                break;

            case MSG_TYPE_MESSAGE_NEW:
                notifyListeners(message);
                break;

            default:
                if (type)
                    console.log('Unknown message type "' + type + '".');
                else
                    console.log('Unknown message "' + message + '".');
        }
    };
    var sendServerMessage = function(serverType, data)
    {
        var message = [serverType];
        if (data) {
            message.push("" + data);
        }

        params.connection.send(JSON.stringify(message));
    };
    var notifyListeners = function(message)
    {
        for (var i=0,L=params.messageListeners.length; i<L; ++i) {
            params.messageListeners[i]( message );
        }
    };

    self.init = function(connUrl, roomId, connectedCb)
    {
        var conn = new WebSocket(connUrl);

        if (conn) {
            params.connection = conn;
        }
        params.roomId = roomId;
        if (connectedCb) {
            params.onConnected = connectedCb;
        }

        conn.onopen = function(e) {
            sendServerMessage(MSG_SERVER_TYPE_ROOM_CONNECT, roomId);
        };

        conn.onmessage = function(e) {
            var parsed = parseMessage(e.data);
            processMessage.apply(self, parsed);
        };
    };

    self.sendMessage = function(message)
    {
        sendServerMessage(MSG_SERVER_TYPE_MESSAGE_ADD, message);
    };

    self.addMessageListener = function(handler)
    {
        params.messageListeners.push(handler);
    };
};

module.exports = Monitor;