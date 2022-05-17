<?php

namespace App\Admin\Actions\Show;

use Dcat\Admin\Actions\Response;
use Dcat\Admin\Show\AbstractTool;
use Dcat\Admin\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Youzan\Shop;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Youzan\Contact;
use App\Exceptions\GrabContactUnauthorizedExcpetion;

class GrabContact extends AbstractTool
{
    /**
     * @return string
     */
	protected $title = '领取线索';

    /**
     * Handle the action request.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function handle(Request $request)
    {
        $token = Cache::get(Shop::$TOKEN_CACHE_KEY);
        if (!$token) {
            return $this->response()->error("请联系管理员录入令牌");
        }

        $shop = Shop::find($this->getKey());

        $entid = $shop->enterprise->qike_enterprise_id;

        if (!$entid) return $this->response()->error('请联系管理员同步企业数据');

        try {
            Artisan::call('youzan:contact',[
                '--token' => $token,
                '--entid' => [$entid]
            ]);
        } catch (GrabContactUnauthorizedExcpetion $e) {
            return $this->response()->error('令牌失效请联系管理员重新录入');
        }

        return $this->response()
            ->success('联系方式获取成功')
            ->refresh();
    }

    /**
	 * @return string|array|void
	 */
	public function confirm()
	{
		return ['确定领取联系方式？', '此举将消耗企客后台线索数量'];
	}

    public function html()
    {
        return <<<HTML
        <div class="btn-group pull-right btn-mini text-white" style="margin-right: 5px">
            <a {$this->formatHtmlAttributes()}>
                <i class="feather icon-message-circle"></i>
                <span class="d-none d-sm-inline">{$this->title()}</span>
            </a>
        </div>
HTML;
    }

}
