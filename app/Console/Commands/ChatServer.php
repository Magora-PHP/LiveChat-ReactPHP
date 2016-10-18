<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Components\ChatMessagesHandler;

class ChatServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chat:serve';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start chat server';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $chat = new ChatMessagesHandler();
        $chat->setMessenger(ChatMessagesHandler::MESSENGER_TYPE_INFO, [$this, 'info']);
        $chat->setMessenger(ChatMessagesHandler::MESSENGER_TYPE_ERROR, [$this, 'error']);

        $app = new \Ratchet\App(env('CHAT_HOST'), env('CHAT_PORT'), env('CHAT_IP'));
        $app->route(env('CHAT_PATH'), $chat, ['*']);

        $this->comment('Server listening at '.env('CHAT_HOST').':'.env('CHAT_PORT'));
        $app->run();
    }
}
