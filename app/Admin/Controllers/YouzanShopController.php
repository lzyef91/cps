<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\YouzanShop;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Show\Tools;
use Dcat\Admin\Widgets\Card;
use Dcat\Admin\Grid\Tools\Selector;
use Dcat\Admin\Http\Controllers\AdminController;
use App\Admin\Actions\Grid\ImportYouzanShops;
use App\Admin\Actions\Grid\InputQikeJwtToken;
use App\Admin\Actions\Grid\ExportYouzanShops;
use App\Admin\Actions\Show\GrabContact;
use App\Models\Youzan\Shop;
use App\Models\Youzan\Category;
use App\Models\Youzan\Contact;
use App\Models\Youzan\Enterprise;
use Dcat\Admin\Widgets\Table;
use Dcat\Admin\Widgets\Modal;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;


class YouzanShopController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new YouzanShop(['brands','categories','enterprise']), function (Grid $grid) {
            // 属性选择器
            $grid->selector(function(Selector $selector){
                // 企业类型选择器
                $s5 = Shop::where('principal_type', 5)->count();
                $s4 = Shop::where('principal_type', 4)->count();
                $s2 = Shop::where('principal_type', 2)->count();
                $s3 = Shop::where('principal_type', 3)->count();
                $selector->selectOne('principal_type', '企业类型', [
                    2 => Shop::$PrincipalType[2].'('.$s2.')',
                    3 => Shop::$PrincipalType[3].'('.$s3.')',
                    5 => Shop::$PrincipalType[5].'('.$s5.')',
                    4 => Shop::$PrincipalType[4].'('.$s4.')',
                ]);

                // 经营类目选择器
                $list = Category::whereRelation('shop', 'deleted_at', NULL)
                            ->selectRaw('count(id) as num, category_name')
                            // ->where('major', 1)
                            ->groupBy('category_name')->orderBy('num','desc')
                            ->limit(15)->pluck('num', 'category_name');;
                $ops = [];
                foreach ($list as $name => $num) {
                    $ops[$name] = $name.'('.$num.')';
                }
                $selector->selectOne('categories.category_name', '经营类目', $ops);

                // 省份选择器
                // $list = Enterprise::selectRaw('region_province_code,region_province,count(distinct qike_enterprise_id) as num')
                //                 ->whereNotNull('region_province_code')->groupBy('region_province_code','region_province')
                //                 ->orderBy('num','desc')->limit(10)->get();

                // $ops = [];
                // foreach ($list as $item) {
                //     $ops[$item->region_province_code] = $item->region_province.'('.$item->num.')';
                // }

                // $selector->selectOne('enterprise.region_province_code', '省份', $ops);

                // 城市选择器
                $list = Enterprise::whereRelation('shop', 'deleted_at', NULL)
                            ->selectRaw('region_city_code,region_city,count(distinct qike_enterprise_id) as num')
                            ->whereNotNull('region_city_code')->groupBy('region_city_code','region_city')
                            ->orderBy('num','desc')->limit(15)->get();

                $ops = [];
                foreach ($list as $item) {
                    $ops[$item->region_city_code] = $item->region_city.'('.$item->num.')';
                }

                $selector->selectOne('enterprise.region_city_code', '城市', $ops);
            });

            // 筛选器
            $grid->filter(function (Grid\Filter $filter) {
                $filter->like('principal_name');
                $filter->equal('principal_type')->multipleSelect(Shop::$PrincipalType);

                $options = Category::select('category_name')->groupBy('category_name')->orderBy('category_name', 'desc')->pluck('category_name','category_name');
                $filter->where('primary_category', function ($query) {
                    $query->whereHas('categories', function ($query) {
                        $query->where('major', 1);
                        $input = $this->input;
                        $query->where(function($query)use($input){
                            foreach ($input as $k => $c) {
                                if ($k == 0) $query->where('category_name', $c);
                                else $query->orWhere('category_name', $c);
                            }
                        });
                    });

                }, '主营类目')->multipleSelect($options);
                $filter->where('secondary_category', function ($query) {
                    $query->whereHas('categories', function ($query) {
                        $query->where('major', 0);
                        $input = $this->input;
                        $query->where(function($query)use($input){
                            foreach ($input as $k => $c) {
                                if ($k == 0) $query->where('category_name', $c);
                                else $query->orWhere('category_name', $c);
                            }
                        });
                    });

                }, '副营类目')->multipleSelect($options);
                $filter->where('own_brand', function($query){
                    $input = $this->input;
                    $query->whereHas('brands', function($query)use($input){
                        $query->where('brand_name', 'like', "%{$input}%")
                            ->orWhere('brand_name_en', 'like', "%{$input}%");
                    });
                }, '旗下品牌');
                $filter->like('enterprise.region_province', '省份');
                $filter->like('enterprise.region_city', '城市');
                $filter->like('enterprise.region_district', '区县');
                $filter->like('name');
                $filter->between('open_at', '店铺创建')->datetime(['format' => 'YYYY-MM-DD']);
            });

            // 表格工具
            $grid->disableEditButton();
            $grid->disableDeleteButton();
            $grid->tools(new InputQikeJwtToken());
            $grid->tools(new ImportYouzanShops());
            $grid->tools(new ExportYouzanShops());

            // 固定列
            $grid->fixColumns(2);

            $grid->paginate(50);

            // 默认排序
            $grid->model()->orderBy('id', 'desc');

            $grid->column('principal_name');

            $grid->column('enterprise.region', '所属区域');

            $grid->column('principal_type')->using(Shop::$PrincipalType);

            $grid->column('name');

            // $grid->column('kdt_id');

            $grid->column('categories')->map(function($cate){
                $major = $cate->major == 1 ? '主营' : '副营';
                $name = $cate->category_name;
                return $major.'|'.$name;
            })->label();

            $grid->brands->map(function($brand){
                if (empty($brand->brand_name)) return $brand->brand_name_en;
                elseif (empty($brand->brand_name_en)) return $brand->brand_name;
                else return $brand->brand_name.'('.$brand->brand_name_en.')';
            })->label('warning');

            // $grid->column('principal_address');

            $grid->column('open_at')->sortable();

            $grid->column('mp_qrcode')
                ->if(function($col){
                    return !empty($col->getValue());
                })
                ->display('查看')
                ->modal(function($modal){
                    $modal->title($this->name.'公众号');
                    $img = $this->mp_qrcode;
                    $card = new Card('', "<img src=\"{$img}\" style=\"width:200px;height:200px\" />");
                    return "<div class='d-flex justify-content-center' style='padding:10px 10px 0'>$card</div>";
                });

            $grid->column('address')->display(function($addr){
                return "<a href='{$addr}' target='_blank'><i class='fa fa-clone'></i>查看</a>";
            });


        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        $model = YouzanShop::with('enterprise');
        return Show::make($id, $model, function (Show $show) {

            $shop = $show->model();
            $show->panel()->tools(function(Tools $tools)use($shop){
                $tools->disableEdit();
                // 领取线索
                if ($shop->has_contacts == Contact::$STATUS[Contact::$WAIT_TO_GRAB]) {
                    $tools->append(new GrabContact());
                }
            });



            $show->field('principal_name');
            $show->field('has_contacts', '联系人')->unescape()->as(function($status)use($shop){
                // 0:没有联系方式 1:有联系方式未领取 2:无法领取 3:未收录 99:已领取
                switch ($status) {
                    case Contact::$STATUS[Contact::$NO_CONTACT]:
                        $txt = "没有联系方式";
                        break;
                    case Contact::$STATUS[Contact::$WAIT_TO_GRAB]:
                        $txt = "有{$this->total_contacts}个联系方式待领取";
                        break;
                    case Contact::$STATUS[Contact::$NO_AUTH]:
                        $txt = "需登录企客后台查看";
                        break;
                    case Contact::$STATUS[Contact::$NO_REPORT]:
                        $txt = "企客没有收录";
                        break;
                    case Contact::$STATUS[Contact::$CONTACT_READY]:
                        $contacts = $shop->contacts;
                        $rows = [];
                        $headers = ['类别','联系人','联系方式','职位','地点','来源'];
                        $rows = [];
                        foreach ($contacts as $c) {
                            $row = [];
                            $row[] = Contact::$CONTACT_TYPE_MAP[$c->contact_type];
                            $row[] = $c->name;
                            $row[] = $c->contact_no;
                            $row[] = $c->duty;
                            $row[] = $c->loaction;
                            $row[] = "<a target=\"_blank\" href=\"{$c->source_url}\">{$c->source_type}</a>";
                            $rows[] = $row;
                        }
                        $txt = Modal::make()->lg()->title("联系人线索")
                                ->body(new Table($headers, $rows))
                                ->button("查看{$this->total_contacts}个联系方式")->render();
                        break;
                    default:
                        $txt = "没有联系方式";
                }
                return $txt;
            });
            $show->field('enterprise.established_at', '公司成立时间');
            $show->field('enterprise.enterprise_status', '企业标签')
                ->unescape()->as(function($status){
                    $pColor = admin_color()->get('primary');
                    $wColor = admin_color()->get('warning');
                    $sColor = admin_color()->get('success');
                    $html = "<span class=\"label\" style=\"background:{$pColor};margin-right:5px;\">{$status}</span>";
                    if ($this->enterprise) {
                        $html .= "<span class=\"label\" style=\"background:{$wColor};margin-right:5px;\">{$this->enterprise->size}</span>";
                        $html .= "<span class=\"label\" style=\"background:{$sColor};\">{$this->enterprise->enterprise_type}</span>";
                    }
                    return $html;
            });
            $show->field('enterprise.enterprise_uniscid', '统一社会信用代码');
            $show->field('enterprise.region', '企业地区');
            $show->field('principal_address');

            $show->divider();

            $show->field('name')->unescape()->as(function($name){
                return "<a target=\"_blank\" href=\"{$this->address}\">{$name}</a>";
            });
            $show->field('kdt_id');
            $show->field('open_at');
            $totalShops = $shop->total_shops;
            if ($totalShops > 1) {
                $show->field('other_shops', '其他店铺')->unescape()->as(function($otherShops)use($totalShops){
                    if (!isset($otherShops)) return '';
                    $shops = Shop::withTrashed()->whereIn('id', $otherShops)->get();
                    $rows = [];
                    for ($i = 0;$i < count($shops);$i += 5) {
                        $row = [];
                        for ($j = 0;$j < 5;$j++) {
                            if (!isset($shops[$i + $j])) break;
                            $shop = $shops[$i + $j];
                            $row[] = "<a target=\"_blank\" href=\"{$shop->address}\">{$shop->name}</a>";
                        }
                        $rows[] = $row;
                    }
                    return Modal::make()->lg()->title("其他店铺目录（共{$totalShops}个）")
                            ->body(new Table([], $rows))
                            ->button("查看其他{$totalShops}个店铺")->render();
                });
            }
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new YouzanShop(), function (Form $form) {
            // 新增页面
            if ($form->isCreating()) {
                $form->text('principal_name')->rules('required|unique:youzan_shops,principal_name');
                $form->select('principal_type')->options([
                    2 => '企业',
                    3 => '个体工商户',
                    4 => '其他组织',
                    5 => '政府及事业单位'
                ])->rules('required');
                $form->text('principal_address');
            }

            // 保存后自动爬取企业信息
            $form->saved(function(Form $form, $res){
                if ($form->isCreating()) {
                    // 自增ID
                    $newId = $res;

                    if (!$newId) {
                        return $form->error('数据保存失败');
                    }

                    // 获取token
                    $token = Cache::get(Shop::$TOKEN_CACHE_KEY);
                    if (!$token) {
                        return $form->error("请先录入令牌");
                    }

                    // 同步主体信息
                    Artisan::call('youzan:ent', [
                        "--token" => $token,
                        "--shopid" => $newId
                    ]);

                    return;
                }
            });
        });
    }
}
