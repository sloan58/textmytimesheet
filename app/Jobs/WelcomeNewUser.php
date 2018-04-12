<?php

namespace App\Jobs;

use App\User;
use Twilio\Rest\Client;
use Illuminate\Bus\Queueable;
use Twilio\Exceptions\TwilioException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class WelcomeNewUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var User
     */
    private $user;

    /**
     * Create a new job instance.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        //
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Twilio\Exceptions\ConfigurationException
     */
    public function handle()
    {
        \Log::info('WelcomeNewUser@handle: Job has been dispatched', []);

        $welcomeMessage = "Hello, you've just been added to TextMyTimeSheet!\n\n" .
                          "TMTS allows you to record your time by sending text messages.  " .
                          "You might want to save this number in your contacts so you can find it later ;-D\n\n" .
                          "Start by getting familiar with the messages that let you add and view your time entries.\n" .
                          "For a list of commands, just text 'commands' to this number\n\n";

        $client = new Client(env('TWILIO_SID'), env('TWILIO_TOKEN'));
//        try {
//            $message = $client->messages->create(
//                $this->user->phone_number,
//                [
//                    'from' => env('TWILIO_DID'),
//                    'body' => $welcomeMessage
//                ]
//            );
//        } catch (TwilioException $e) {
//            \Log::error('WelcomeNewUser@handle: error sending Welcome SMS', [$e->getMessage(), $this->user->phone_number]);
//            exit;
//        }

//        \Log::info('WelcomeNewUser@handle: Welcome Text sent successfully', [$message->sid]);
    }
}
