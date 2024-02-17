<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mobizon\MobizonApi;

class SendVerificationCode implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $phone_number;
    protected $code;
    /**
     * Create a new job instance.
     */
    public function __construct(string $phone_number, string $code)
    {
        $this->phone_number = $phone_number;
        $this->code = $code;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $api = new MobizonApi([
            'apiKey' => env('MOBIZON_API_KEY'),
            'apiServer' => env('MOBIZON_API_SERVER', 'https://api.mobizon.kz')
        ]);

        $api->call('message', 'sendSMSMessage', [
            'recipient' => $this->phone_number,
            'text' => "Ваш код верификации: {$this->code}"
        ]);
    }
}
