<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Youzan\Contact;
use App\Exceptions\GrabContactUnauthorizedExcpetion;

class GrabContact extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'youzan:contact {--token= : 企客JWTtoken} {--entid=* : 企客企业ID数组}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '获取企业联系人信息';

    protected $qikeToken;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $token = $this->option('token');
        if (empty($token)) {
            $this->error('缺少参数token');
            exit();
        }

        $entids = $this->option('entid');
        if (empty($entids)) {
            $this->error('缺少参数entid');
            exit();
        }

        $this->qikeToken = $token;

        $total = count($entids);
        $bar = $this->output->createProgressBar($total);
        $bar->start();
        $failCount = 0;

        foreach ($entids as $entid) {
            // 检索数据库
            $ent = DB::table('youzan_enterprises')->where('qike_enterprise_id', $entid)->first();
            if (!$ent) {
                Log::error("GrabContactException: cant find enterprise");
                $failCount++;
                continue;
            }

            // 获取联系方式
            $res = $this->grab($ent);

            if ($res == 'fail') $failCount++;

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $successCount = $total - $failCount;
        $this->info("成功率：{$successCount}/{$total}");
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
            $msg = "GrabContactProcessFailedException: qike_enterprise_id {$ent->qike_enterprise_id} {$e->getMessage()}";
            Log::error("GrabContactException: {$msg}");
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
            Log::error("GrabContactException: {$code} {$msg}");
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
