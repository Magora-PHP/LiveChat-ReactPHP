<div class="messages-item col-md-12">
    <div class="message-meta">
        <div class="message-user-name">{{ $msg->user->name }}</div>
        <div class="message-user-nickname">{{ $msg->user->nickname }}</div>
        <div class="message-date pull-right">{{ date('d/m/Y H:i', strtotime($msg->created_at)) }}</div>
    </div>
    <div class="message-body">
        {!! nl2br($msg->body) !!}
    </div>
</div>