<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class RestrictYouzanShop extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'youzan:restrict';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '去重有赞店铺主体';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $count = DB::table('youzan_shops')->selectRaw('principal_name, count(id) as num')
                    ->whereNotNull('principal_name')
                    // ->whereNull('deleted_at')
                    ->groupBy('principal_name')
                    ->having('num', '>' , 1)
                    ->count();
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        // 检索同主体多店铺
        DB::table('youzan_shops')->selectRaw('principal_name, count(id) as num')
            ->whereNotNull('principal_name')
            // ->whereNull('deleted_at')
            ->groupBy('principal_name')
            ->having('num', '>' , 1)
            ->orderBy('num', 'desc')
            ->chunk(100, function($shops)use($bar){
                foreach ($shops as $shop) {
                    // 检索同主体店铺
                    $ids = DB::table('youzan_shops')->where('principal_name', $shop->principal_name)
                            // ->whereNull('deleted_at')
                            ->orderBy('open_at', 'asc')->pluck('id');
                    // 保留最早的店铺
                    $firstId = $ids[0];
                    // 其他店铺
                    $ids->shift();
                    // 留存店铺
                    DB::table('youzan_shops')->where('id', $firstId)->update([
                        'total_shops' => $shop->num,
                        'other_shops' => $ids,
                        'deleted_at' => NULL
                    ]);
                    // 删除其他店铺
                    DB::table('youzan_shops')->whereIn('id', $ids)->update([
                        'total_shops' => 1,
                        'other_shops' => NULL,
                        'deleted_at' => Carbon::now()
                    ]);

                    $bar->advance();
                }
            });

        // 删除个体店铺
        DB::table('youzan_shops')->where('principal_type', 1)->update(['deleted_at' => Carbon::now()]);

        $bar->finish();
        $this->newLine();
    }
}
