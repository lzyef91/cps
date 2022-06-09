<?php

namespace App\Admin\Forms;

use Dcat\Admin\Widgets\Form;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Contracts\LazyRenderable;
use App\Exports\YouzanShopExport;
use App\Models\Youzan\Shop;
use App\Models\Youzan\Enterprise;
use App\Jobs\BatchGrabContacts;
use App\Models\Youzan\Contact;
use Illuminate\Support\Facades\Cache;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use App\Jobs\NotifyExportResult;
use App\Models\Export;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Dcat\Admin\Admin;

class ExportYouzanShopsForm extends Form implements LazyRenderable
{
    use LazyWidget;

    public $options = [
        1 => '主体名称',
        2 => '手机线索',
        3 => '主营类目',
        4 => '副营类目',
        5 => '所在地区',
        6 => '公司规模',
        7 => '公司类型',
        8 => '公司成立时间',
        9 => '统一社会信用代码',
        10 => '旗下品牌',
        11 => '店铺名称',
        12 => '店铺地址',
        13 => '店铺公众号'
    ];
    /**
     * Handle the form request.
     *
     * @param array $input
     *
     * @return mixed
     */
    public function handle(array $input)
    {
        // 获取筛选条件
        $payload = $this->payload['q'] ?? [];
        $payload = array_merge($payload, $input);

        // 生成导出记录
        // 文件名
        $excelName = 'youzan_shops_export_'.Carbon::now()->format('YmdHis').'.csv';
        // 文件存储盘
        $disk = 'excel';
        // uuid
        $uuid = Str::uuid();
        $exportRecord = new Export();
        // 申请人
        $exportRecord->admin_user_id = Admin::user()->id;
        // 导出项
        $exportRecord->exported_fields = $this->parseOptions($payload['export_fields']);
        // 申请时间
        $exportRecord->start_at = Carbon::now();
        // 文件名
        $exportRecord->name = $excelName;
        // 文件相对路径
        $exportRecord->path = $excelName;
        $exportRecord->disk = $disk;
        $exportRecord->uuid = $uuid;

        // 导出excel任务对象
        $payload['export_uuid'] = $uuid;
        $exportJob = new YouzanShopExport($payload, $payload['grab_new'] != 'yes');

        // 拉取新线索
        if ($payload['grab_new'] == 'yes' && $this->buildGrabQuery($payload)->count() > 0) {
            // 令牌有效性
            if (!$token = Cache::get(Shop::$TOKEN_CACHE_KEY)) {
                return $this->response()->error("请先录入令牌");
            }

            // 批量领取联系方式
            $batches = [];
            $query = $this->buildGrabQuery($payload);
            $query->chunkById(50, function($shops)use(&$batches, $token){
                $ids = $shops->pluck('id')->all();
                $ents = Enterprise::whereIn('shop_id', $ids)->get();
                $batches[] = new BatchGrabContacts($ents, $token);
            });

            // 批处理领取联系方式
            $batch = Bus::batch($batches)->then(function(Batch $batch)
            use($exportJob,$uuid,$excelName,$disk){
                // 处理完成后导出
                $exportJob->store($excelName, $disk)->chain([
                    new NotifyExportResult($uuid, true)
                ]);
            })->catch(function(Batch $batch, \Throwable $e)use($uuid){
                // 发生第一个错误触发
                NotifyExportResult::dispatch($uuid,false,get_class($e),$e->getMessage());
            })->dispatch();

            // 保存批处理ID
            $exportRecord->batch_id = $batch->id;
        } else {
            // 不拉取线索直接导出
            $exportJob->store($excelName, $disk)->chain([
                new NotifyExportResult($uuid, true)
            ]);
        }

        // 新增导出记录
        $exportRecord->save();

        return $this->response()
				->success('成功提交导出任务')
				->redirect('exports');
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        // 计算消耗的线索
        $payload = $this->payload['q'] ?? null;
        $totalGrab = $this->buildGrabQuery($payload)->count();
        // 计算已有线索量
        $totalExist = $this->buildGrabQuery($payload, true)->count();
        // 提示
        $content = "此次导出包含已有线索{$totalExist}条/待领取线索{$totalGrab}条";
        if ($totalGrab > 0) $content .= "，如果勾选拉取新线索，则此次导出将消耗企客多{$totalGrab}条线索";
        $this->confirm('确认导出', $content);

        $this->checkbox('export_fields', '导出项目')->options($this->options)->required();
        $this->radio('grab_new', '拉取线索')->options(['yes'=>'拉取新线索', 'no'=>'导出已有线索'])->required();
    }

    protected function parseOptions($exportFields)
    {
        $res = [];
        foreach ($exportFields as $f) {
            $res[] = $this->options[$f];
        }
        return $res;
    }

    /**
     * The data of the form.
     *
     * @return array
     */
    public function default()
    {
        return [
            'export_fields'  => [1,2,3,4,5,6,7,8,9,10,11,12,13],
            'grab_new' => 'no'
        ];
    }

    /**
     * 构建待领取联系方式的查询
     */
    protected function buildGrabQuery($queryParams, $onlyExistedData = false)
    {
        $query = Shop::query();

        if ($onlyExistedData) $query->where('has_contacts', Contact::$STATUS[Contact::$CONTACT_READY]);
        else $query->where('has_contacts', Contact::$STATUS[Contact::$WAIT_TO_GRAB]);

        if (!$queryParams) return $query;

        // 主体名称 string
        $principalName = $queryParams['principal_name'] ?? NULL ?: NULL;
        if ($principalName) {
            $query->where('principal_name', 'like', "%{$principalName}%");
        }

        // 主体类型 array
        $principalType = $queryParams['principal_type'] ?? NULL ?: NULL;
        if ($principalType) {
            $query->whereIn('principal_type', $principalType);
        }

        // 主营类目 array
        $primaryCategory = $queryParams['primary_category'] ?? NULL ?: NULL;
        if ($primaryCategory) {
            $query->whereHas('categories', function ($query)use($primaryCategory) {
                $query->where('major', 1);
                $query->where(function($query)use($primaryCategory){
                    foreach ($primaryCategory as $k => $c) {
                        if ($k == 0) $query->where('category_name', $c);
                        else $query->orWhere('category_name', $c);
                    }
                });
            });
        }
        // 副营类目 array
        $secondaryCategory = $queryParams['secondary_category'] ?? NULL ?: NULL;
        if ($secondaryCategory) {
            $query->whereHas('categories', function ($query)use($secondaryCategory) {
                $query->where('major', 0);
                $query->where(function($query)use($secondaryCategory){
                    foreach ($secondaryCategory as $k => $c) {
                        if ($k == 0) $query->where('category_name', $c);
                        else $query->orWhere('category_name', $c);
                    }
                });
            });
        }
        // 品牌 array
        $ownBrand = $queryParams['own_brand'] ?? NULL ?: NULL;
        if ($ownBrand) {
            $query->whereHas('brands', function($query)use($ownBrand){
                $query->where('brand_name', 'like', "%{$ownBrand}%")
                    ->orWhere('brand_name_en', 'like', "%{$ownBrand}%");
            });
        }
        // 企业信息 array
        $enterprise = $queryParams['enterprise'] ?? NULL ?: NULL;
        if ($enterprise) {
            $query->whereHas('enterprise',function($query)use($enterprise){
                if (isset($enterprise['region_province'])) $query->where('region_province', 'like', "%{$enterprise['region_province']}%");
                if (isset($enterprise['region_city'])) $query->where('region_city', 'like', "%{$enterprise['region_city']}%");
                if (isset($enterprise['region_district'])) $query->where('region_district', 'like', "%{$enterprise['region_district']}%");
            });
        }
        // 店铺名称
        $name = $queryParams['name'] ?? NULL ?: NULL;
        if ($name) {
            $query->where('name', 'like', "%{$name}%");
        }
        // 开店时间
        $openAt = $queryParams['open_at'] ?? NULL ?: NULL;
        if ($openAt) {
            $query->where(function($query)use($openAt){
                if (isset($openAt['start'])) $query->where('open_at', '>=', $openAt['start']);
                if (isset($openAt['end'])) $query->where('open_at', '<', $openAt['end']);
            });
        }
        // 属性筛选器
        $selector = $queryParams['_selector'] ?? NULL ?: NULL;
        if ($selector) {
            // 主体类型
            if (isset($selector['principal_type'])) $query->where('principal_type', $selector['principal_type']);
            // 类目名称
            if (isset($selector['categories_category_name'])) $query->whereRelation('categories', 'category_name', $selector['categories_category_name']);
            // 所在城市
            if (isset($selector['enterprise_region_city_code'])) $query->whereRelation('enterprise', 'region_city_code', $selector['enterprise_region_city_code']);
        }

        return $query;
    }
}
