<?php

namespace App\Imports;

use App\Models\Youzan\Shop;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class YouzanShopImport implements ToModel, WithBatchInserts, WithChunkReading
{

    use Importable, RemembersRowNumber;

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // 跳过说明
        if (!isset($row[1])) return NULL;

        // 店铺名称
        $name = Str::of($row[0])->trim()->trim('\n')->trim('\r')->trim('\r\n');
        // 跳过表头，店铺名称不可为空
        if (empty($name) || $name == '店铺名称') return NULL;

        // 店铺地址
        $addr = Str::of($row[1])->trim()->trim('\n')->trim('\r')->trim('\r\n');
        // 店铺地址不可为空
        if (empty($addr)) return NULL;

        // 店铺ID
        $id = (int)$addr->match('/kdt_id=(\d+)/')->toString();
        // 店铺地址不合法
        if($id == 0) return NULL;

        // 公众号二维码
        $qr = Str::of($row[2])->trim()->trim('\n')->trim('\r')->trim('\r\n');

        // 店铺名称和地址不可为空
        if (empty($name) || empty($addr)) return NULL;

        // 排重
        if (DB::table('youzan_shops')->where('kdt_id', $id)->exists()) return NULL;

        return new Shop([
            'name' => $name,
            'kdt_id' => $id,
            'address' => $addr,
            'mp_qrcode' => $qr ?: NULL,
        ]);
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function batchSize(): int
    {
        return 100;
    }
}
