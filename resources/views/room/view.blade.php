@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-8 col-md-offset-2">

            <div class="form-group">
                <button class="btn btn-default btn-sm" data-toggle="collapse" data-target="#messageNewPanel" aria-expanded="false">
                    <i class="fa fa-plus"></i> Add new message
                </button>
            </div>
            <div id="messageNewPanel" class="panel panel-default message-new-panel collapse">
                <form class="form-horizontal" role="form" method="POST" data-action="{{ url('/messages/create') }}">
                    {{ csrf_field() }}

                    <input type="hidden" name="room_id" value="{{ $room->id }}">

                    <div class="form-group">
                        <div class="col-md-12">
                            <textarea name="body" id="messageBody" rows="3" class="form-control"></textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-success" onclick="message.post();">Post</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="panel panel-default">
                <div id="messages" class="panel-body messages">
                    @foreach ($room->messages as $msg)
                        @include('room.message-item')
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <script>setTimeout(function() {
        monitor.init("{{ $chatConnUrl }}", {{ $room->id }}, function() {
            monitor.addMessageListener(message.onNewMessage);
            message.setMonitored(true);
            message.notifyMessageServer = monitor.sendMessage;
        });
    }, 1000);</script>
@endsection
