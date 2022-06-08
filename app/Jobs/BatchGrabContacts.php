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

    // public $failOnTimeout = true;

    public $tries = 1;

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

        $this->grab();
    }

    protected function grab()
    {
        // 获取批量ID
        $entids = $this->ents->pluck('qike_enterprise_id')->toArray();
        $payload = ['python3', 'contact_batch_crawler.py', $this->qikeToken];
        $payload = array_merge($payload, $entids);

        // 调用python获取数据
        try {
            $process = new Process($payload, env('PYTHON_WORK_DIR'));
            $process->setTimeout(3600);
            $process->start();
            $process->wait();
            $res = $process->getOutput();
        } catch (\Exception $e) {
            $msg = "ProcessFailedException: {$e->getMessage()}";
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

        foreach($res['data'] as $contact) {
            $shopid = $this->ents->firstWhere('qike_enterprise_id',$contact['entid'])->shop_id;

            // 手机号
            foreach ($contact['mobile'] as $m) {
                DB::table('youzan_contacts')->updateOrInsert(
                    [
                        'shop_id'=> $shopid,
                        'qike_contact_id' => $m['qike_contact_id'],
                        'contact_type' => 1
                    ],
                    $m
                );
            }

            // 固话
            foreach ($contact['phone'] as $p) {
                DB::table('youzan_contacts')->updateOrInsert(
                    [
                        'shop_id'=> $shopid,
                        'qike_contact_id' => $p['qike_contact_id'],
                        'contact_type' => 2
                    ],
                    $p
                );
            }

            // email
            foreach ($contact['email'] as $e) {
                DB::table('youzan_contacts')->updateOrInsert(
                    [
                        'shop_id'=> $shopid,
                        'qike_contact_id' => $e['qike_contact_id'],
                        'contact_type' => 3
                    ],
                    $e
                );
            }

            // 更改状态
            DB::table('youzan_shops')->where('id', $shopid)->update([
                'has_contacts' => Contact::$STATUS[Contact::$CONTACT_READY]
            ]);
        }

        return 'success';
    }
}
