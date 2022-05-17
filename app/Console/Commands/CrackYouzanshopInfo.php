<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CrackYouzanshopInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'youzan:crawler';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '爬取有赞店铺数据';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $count = DB::table('youzan_shops')->whereNull('open_at')->whereNotNull('kdt_id')
                    ->whereNull('deleted_at')->count();
        $bar = $this->output->createProgressBar($count);
        $bar->start();
        $failCount = 0;

        DB::table('youzan_shops')->whereNull('open_at')->whereNotNull('kdt_id')
            ->whereNull('deleted_at')
            ->chunkById(100, function($shops) use ($bar,&$failCount){
                foreach($shops as $shop){
                    // 调用python获取数据
                    try {
                        $process = new Process(['python3', 'crawler.py', $shop->kdt_id], env('PYTHON_WORK_DIR'));
                        $process->start();

                        $process->wait();

                        $res = $process->getOutput();
                    } catch (ProcessTimedOutException $e) {
                        Log::error("CrackYouzanShopInfoTimeout: shop {$shop->id} {$shop->name}");
                        continue;
                    }

                    // 获取失败
                    if (empty($res)) {
                        $failCount++;
                        $bar->advance();
                        continue;
                    }

                    // 解码数据
                    $res = json_decode($res, true);

                    // 开店时间
                    $update = ['open_at' => Carbon::createFromTimestampMs($res['home']['applyTime'])];

                    // 个体类型店铺进行软删除
                    $deleteAt = NULL;

                    // 店铺名称
                    $shopName = $res['home']['shopName'];
                    if (!empty($shopName)) {
                        $update = array_merge($update, ['name' => $shopName]);
                    }

                    if ($res['home']['principalCertRecordResult']['status'] == 4) {
                        // 主体信息
                        $name = $res['principal']['principalName'];
                        $type = $res['principal']['subjectCertType'];
                        // 个体类型店铺或主体为空的店铺进行软删除
                        if ($type == 1 || empty($name)) {
                            $deleteAt = Carbon::now();
                        }
                        $addr = $res['principal']['address'] ?? '';
                        $update = array_merge($update, [
                            'principal_name' => $name,
                            'principal_type' => $type,
                            'principal_address' => $addr
                        ]);

                        // 类目信息
                        if (isset($res['principal']['categoryCertInfo'])) {
                            foreach ($res['principal']['categoryCertInfo'] as $cate) {
                                $cateName = $cate['categoryName'];
                                $cateCode = $cate['categoryCode'];
                                $major = $cate['majar'];
                                // 更新或插入
                                DB::table('youzan_shop_categories')->updateOrInsert(
                                    ['shop_id' => $shop->id, 'category_code' => $cateCode],
                                    ['category_name' => $cateName, 'major' => $major]
                                );
                            }
                        }
                    } else {
                        // 主体不存在的店铺软删除
                        $deleteAt = Carbon::now();
                    }

                    if ($res['home']['brandCertStatus'] == 4) {
                        // 品牌信息
                        foreach ($res['brand']['brandCertClientDTOS'] as $brand) {
                            $brandName = $brand['brandName'] ?? $res['home']['shopName'] ?: $res['home']['shopName'];
                            $brandNameEn = $brand['brandNameEN'] ?? NULL ?: NULL;
                            $brandCertType = $brand['brandCertType'] ?? NULL ?: NULL;
                            $brandAuthLevel = $brand['authorizationLevel'] ?? NULL ?: NULL;
                            $brandCategory = $brand['tradeMarkCategory'] ?? NULL ?: NULL;
                            $validTime = $brand['validTime'] ?? NULL ?: NULL;
                            // 更新或插入
                            DB::table('youzan_shop_brands')->updateOrInsert(
                                ['shop_id' => $shop->id, 'brand_name' => $brandName, 'brand_auth_level' => $brandAuthLevel],
                                ['brand_name_en' => $brandNameEn, 'brand_cert_type' => $brandCertType,
                                 'brand_category' => $brandCategory, 'valid_time' => $validTime]
                            );
                        }
                    }

                    // 更新数据库
                    $update = array_merge($update, ['deleted_at' => $deleteAt]);
                    DB::table('youzan_shops')->where('id', $shop->id )->update($update);

                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine();
        $successCount = $count - $failCount;
        $this->info("成功率：{$successCount}/{$count}");
    }
}
