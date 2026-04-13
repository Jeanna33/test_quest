<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WbApiService;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class SyncOrders extends Command
{
    protected $signature = 'sync:orders';
    protected $description = 'Sync orders from external API';

    public function handle()
    {
        ini_set('memory_limit', '512M');

        $api = new WbApiService();

        $page = 1;
        $lastPage = 1;

        $totalApi = 0;
        $totalDb = 0;

        do {
            $this->info("Orders Page: $page");

            try {
                $data = $api->orders([
                    'page' => $page,
                    'limit' => 500,
                ]);
            } catch (\Exception $e) {
                $this->error("API error: " . $e->getMessage());
                sleep(5);
                continue;
            }

            if (!isset($data['data'])) {
                $this->error("Invalid response page $page");
                sleep(3);
                continue;
            }

            $items = $data['data'];
            $lastPage = $data['meta']['last_page'] ?? $page;

            $rows = [];

            foreach ($items as $item) {
                $rows[] = [
                    'g_number' => $item['g_number'] ?? null,
                    'date' => $item['date'] ?? null,
                    'last_change_date' => $item['last_change_date'] ?? null,

                    'supplier_article' => $item['supplier_article'] ?? null,
                    'tech_size' => $item['tech_size'] ?? null,
                    'barcode' => $item['barcode'] ?? null,

                    'total_price' => $item['total_price'] ?? 0,
                    'discount_percent' => $item['discount_percent'] ?? 0,

                    'warehouse_name' => $item['warehouse_name'] ?? null,
                    'oblast' => $item['oblast'] ?? null,

                    'income_id' => $item['income_id'] ?? null,
                    'odid' => $item['odid'] ?? null,

                    'nm_id' => $item['nm_id'] ?? null,

                    'subject' => $item['subject'] ?? null,
                    'category' => $item['category'] ?? null,
                    'brand' => $item['brand'] ?? null,

                    'is_cancel' => $item['is_cancel'] ?? false,
                    'cancel_dt' => $item['cancel_dt'] ?? null,

                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $totalApi += count($items);

            foreach (array_chunk($rows, 200) as $chunk) {
                DB::table('orders')->insert($chunk);
                $totalDb += count($chunk);
            }

            unset($rows, $items);
            gc_collect_cycles();

            $page++;
            sleep(2);

        } while ($page <= $lastPage);

        $this->info("ORDERS DONE");
        $this->info("API: $totalApi");
        $this->info("DB: $totalDb");
    }


}
