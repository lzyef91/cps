<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Illuminate\Support\Facades\Log;
use App\Models\Youzan\Contact;

class CrawlEntInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'youzan:ent {--token= : 企客JWTtoken} {--shopid= : 主体ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '从企客后台爬取企业信息';

    protected $qikeToken = '';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // jwt token有效期24小时
        $token = $this->option('token');
        if (empty($token)) {
            $this->error('缺少参数token');
            exit();
        }

        $this->qikeToken = $token;

        $shopid = $this->option('shopid');

        if (!empty($shopid)) {
            $this->forceCraw($shopid);
        } else {
            $this->crawAll();
        }


    }

    /**
     * 新增或更新店铺的企业信息
     */
    protected function forceCraw($shopid)
    {
        $shop = DB::table('youzan_shops')->find($shopid);
        if (isset($shop)) {
            $res = $this->craw($shop);
            if ($res == 'fail') $this->error("{$shopid} {$shop->name}企业信息获取失败");
            else $this->info("{$shopid} {$shop->name}企业信息获取成功");
        } else {
            $this->error("{$shopid}店铺不存在");
        }
    }

    /**
     * 新增或更新所有非个体店铺的未同步企业信息
     */
    protected function crawAll()
    {
        $count = DB::table('youzan_shops')->where('principal_type', '>', 1)->whereNull('deleted_at')
                    ->whereNull('has_contacts')->whereNotNull('principal_name')->count();
        $bar = $this->output->createProgressBar($count);
        $bar->start();
        $failCount = 0;

        // 查询主体存在非个体的店铺
        DB::table('youzan_shops')->where('principal_type', '>', 1)->whereNull('deleted_at')
            ->whereNull('has_contacts')->whereNotNull('principal_name')
            ->chunkById(100, function($shops) use ($bar,&$failCount){
                foreach($shops as $shop){
                    // 处理数据
                    $res = $this->craw($shop);
                    // 处理结果
                    if ($res == 'fail') $failCount++;
                    // 同步进度
                    $bar->advance();
                }
        });

        $bar->finish();
        $this->newLine();
        $successCount = $count - $failCount;
        $this->info("成功率：{$successCount}/{$count}");
    }

    protected function craw($shop)
    {
        // 调用python获取数据
        try {
            $process = new Process(['python3', 'enterprise_info_crawler.py', $shop->principal_name, $this->qikeToken], env('PYTHON_WORK_DIR'));
            $process->start();
            $process->wait();
            $res = $process->getOutput();
        } catch (ProcessTimedOutException $e) {
            Log::error("CrawEntInfoTimeout: shop {$shop->id} {$shop->name}");
            return 'fail';
        }

        // 解码数据
        $res = json_decode($res, true);

        // 处理响应
        if ($res['status_code'] == 401) {
            // token失效
            Log::error('CrawEntInfo: token invalid');
            // 退出程序
            die('CrawEntInfo: token invalid');
        } elseif ($res['status_code'] == 404) {
            // 企客未收录企业
            DB::table('youzan_shops')->where('id', $shop->id)->update(
                [
                    'has_contacts' => Contact::$STATUS[Contact::$NO_REPORT],
                    'total_contacts' => 0
                ]
            );
            return 'success';
        } elseif ($res['status_code'] != 200) {
            Log::error('CrawEntInfoException: '.$res['msg']);
            return 'fail';
        }

        $res = $res['data'];

        // 录入标记
        $hasContacts = NULL;
        if ($res['contact_count'] == 0) {
            // 没有联系方式
            $hasContacts = Contact::$STATUS[Contact::$NO_CONTACT];
        }
        if (($res['view_status'] == 0 || $res['view_status'] == 1)
             && $res['contact_count'] > 0) {
            // 有联系方式未领取
            $hasContacts = Contact::$STATUS[Contact::$WAIT_TO_GRAB];
        }
        if ($res['view_status'] > 1 && $res['contact_count'] > 0) {
            // 无法领取
            $hasContacts = Contact::$STATUS[Contact::$NO_AUTH];;
        }

        // 更改店铺状态
        DB::table('youzan_shops')->where('id', $shop->id)->update(
            [
                'has_contacts' => $hasContacts,
                'total_contacts' => $res['contact_count']
            ]
        );

        $entinfo = [
            'region_code' => $res['region_code'] ?? NULL ?: NULL,
            'region' => $res['region'] ?? NULL ?: NULL
        ];

        if (isset($res['region_code']) && !empty($res['region_code'])) {
            // 查找企业区域信息
            $area = DB::table('china_areas')->where('code', $res['region_code'])->first();
            if (!isset($area)) {
                // 查找修正映射
                $fix = DB::table('china_fixed_areas')->where('code', $res['region_code'])->first();
                if (isset($fix)) {
                    // 查找企业信息
                    $area = DB::table('china_areas')->where('code', $fix->fixed_code)->first();
                }
            }

            if (isset($area)) {
                $entinfo = array_merge($entinfo, [
                    'region_code' => $area->code,
                    'region' => $area->region,
                    'region_province_code' => $area->province_code,
                    'region_province' => $area->province,
                    'region_city_code' => $area->city_code,
                    'region_city' => $area->city,
                    'region_district_code' => $area->district_code,
                    'region_district' => $area->district
                ]);
            }
        }

        // 录入企业信息
        $entinfo = array_merge($entinfo, [
            'qike_enterprise_id' => $res['qike_enterprise_id'],
            'enterprise_type' => $res['enterprise_type'] ?? NULL ?: NULL,
            'enterprise_status' => $res['enterprise_status'] ?? NULL ?: NULL,
            'enterprise_uniscid' => $res['enterprise_uniscid'] ?? NULL ?: NULL,
            'legal_person_name' => $res['legal_person_name'] ?? NULL ?: NULL,
            'size' => $res['size'] ?? NULL ?: NULL,
            'established_at' => $res['established_at'] ?? NULL ?: NULL
        ]);
        DB::table('youzan_enterprises')->updateOrInsert(
            ['shop_id' => $shop->id],
            $entinfo
        );

        return 'success';
    }
}
