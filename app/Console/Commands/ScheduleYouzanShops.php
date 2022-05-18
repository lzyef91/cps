<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\Youzan\Shop;
use Illuminate\Support\Facades\Log;

class ScheduleYouzanShops extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'youzan:schedule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '周期性维护拓客数据任务';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            // 爬取有赞店铺信息
            $this->info('crawler youzan shops start...');

            $isFinished = false;
            while (!$isFinished) {
                $this->call('youzan:crawler');
                // 判定是否已经完成爬取
                $count = DB::table('youzan_shops')->whereNull('open_at')->whereNotNull('kdt_id')
                        ->whereNull('deleted_at')->count();
                if ($count == 0) $isFinished = true;
            }

            $this->info('crawler youzan shops complete!');

            // 去掉脏数据
            $this->info('restrict youzan shops start...');
            $this->call('youzan:restrict');
            $this->info('restrict youzan shops start...');

            // 企客爬取企业信息
            $this->info('crawler enterprises start...');

            $isFinished = false;
            $token = Cache::get(Shop::$TOKEN_CACHE_KEY);
            if (!$token) {
                $this->error('empty token');
                Log::error("ScheduleYouzanShopsException: empty token");
                exit();
            }

            while (!$isFinished) {
                $this->call('youzan:ent', [
                    '--token' => $token
                ]);
                // 判定是否已经完成爬取
                $count = DB::table('youzan_shops')->where('principal_type', '>', 1)->whereNull('deleted_at')
                        ->whereNull('has_contacts')->whereNotNull('principal_name')->count();
                if ($count == 0) $isFinished = true;
            }

            $this->info('crawler enterprises complete!');
        } catch (\Exception $e) {
            $cls = get_class($e);
            $msg = $e->getMessage();
            $this->error($msg);
            Log::error("ScheduleYouzanShopsException: {$cls} {$msg}");
            exit();
        }

    }
}
