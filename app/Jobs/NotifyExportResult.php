<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class NotifyExportResult implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $uuid;
    protected $isSuccess;
    protected $exception;
    protected $exceptionMsg;

    /**
     * Create a new job instance.
     * @var uuid App\Models\Youzan\Export模型的uuid
     * @var exception 异常类的的类名
     * @var exceptionMsg 异常的错误信息
     * @return void
     */
    public function __construct(string $uuid, bool $isSuccess,
        string $exception = null, string $exceptionMsg = null)
    {
        $this->uuid = $uuid;
        $this->isSuccess = $isSuccess;
        $this->exception = $exception;
        $this->exceptionMsg = $exceptionMsg;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $now = Carbon::now();
        if ($this->isSuccess) {
            DB::table('exports')->updateOrInsert(
                ['uuid' => $this->uuid],
                [
                    'succeed_at'=> $now,
                    'finished_at' => $now
                ]
            );
        } else {
            DB::table('exports')->updateOrInsert(
                ['uuid' => $this->uuid],
                [
                    'exception' => $this->exception,
                    'exception_msg' => $this->exceptionMsg,
                    'failed_at' => $now,
                    'finished_at' => $now]
            );
        }
    }
}
