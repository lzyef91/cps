<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Youzan\Contact;
use App\Exceptions\GrabContactUnauthorizedExcpetion;

class BatchGrabContacts implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $ents;

    protected $qikeToken;

    public $timeout = 1200;

    public $failOnTimeout = true;

    public $tries = 3;

    /**
     * Create a new job instance.
     * @param Illuminate\Database\Eloquent\Collection ents
     * @var ents 长度最大100个
     * @param string qikeToken
     * @var qikeToken 企客JwtToken
     * @return void
     */
    public function __construct(Collection $ents, String $qikeToken)
    {
        $this->ents = $ents;
        $this->qikeToken = $qikeToken;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->batch()->cancelled()) {
            return;
        }

        foreach ($this->ents as $ent) {
            $this->grab($ent);
        }
    }

    protected function grab($ent)
    {
        // 调用python获取数据
        try {
            $process = new Process(['python3', 'contact_crawler.py', $ent->qike_enterprise_id, $this->qikeToken], env('PYTHON_WORK_DIR'));
            $process->start();
            $process->wait();
            $res = $process->getOutput();
        } catch (\Exception $e) {
            $msg = "ProcessFailedException: qike_enterprise_id {$ent->qike_enterprise_id} {$e->getMessage()}";
            Log::error("BatchGrabContactException: {$msg}");
            return 'fail';
        }

        // 解码数据
        $res = json_decode($res, true);

        // 发生错误
        if ($res['status_code'] != 200) {
            if ($res['status_code'] == 401) {
                throw new GrabContactUnauthorizedExcpetion('token invalid');
            }
            $code = $res['status_code'];
            $msg = $res['msg'];
            Log::error("BatchGrabContactException: {$code} {$msg}");
            return 'fail';
        }

        // 手机号
        foreach ($res['data']['mobile'] as $m) {
            DB::table('youzan_contacts')->updateOrInsert(
                [
                    'shop_id'=> $ent->shop_id,
                    'qike_contact_id' => $m['qike_contact_id'],
                    'contact_type' => 1
                ],
                $m
            );
        }

        // 固话
        foreach ($res['data']['phone'] as $m) {
            DB::table('youzan_contacts')->updateOrInsert(
                [
                    'shop_id'=> $ent->shop_id,
                    'qike_contact_id' => $m['qike_contact_id'],
                    'contact_type' => 2
                ],
                $m
            );
        }

        // email
        foreach ($res['data']['email'] as $m) {
            DB::table('youzan_contacts')->updateOrInsert(
                [
                    'shop_id'=> $ent->shop_id,
                    'qike_contact_id' => $m['qike_contact_id'],
                    'contact_type' => 3
                ],
                $m
            );
        }

        // 更改状态
        DB::table('youzan_shops')->where('id', $ent->shop_id)->update([
            'has_contacts' => Contact::$STATUS[Contact::$CONTACT_READY]
        ]);

        return 'success';
    }
}
