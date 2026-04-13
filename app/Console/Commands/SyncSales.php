<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WbApiService;
use Illuminate\Support\Facades\DB;
use App\Models\Sale;

class SyncSales extends Command
{
    protected $signature = 'sync:sales';
    protected $description = 'Sync sales from external API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ini_set('memory_limit', '512M');

        $api = new WbApiService();

        $page = 1;
        $lastPage = 1;

        $totalApi = 0;
        $totalDb = 0;

        do {
            $this->info("Page: $page");

            try {
                $data = $api->sales([
                    'page' => $page,
                    'limit' => 500,
                ]);
            } catch (\Exception $e) {
                $this->error("API error page $page: " . $e->getMessage());
                sleep(5);
                continue;
            }

            if (!isset($data['data'])) {
                $this->error("Invalid API response page $page");
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

                    'is_supply' => (bool)($item['is_supply'] ?? false),
                    'is_realization' => (bool)($item['is_realization'] ?? false),

                    'promo_code_discount' => $item['promo_code_discount'] ?? null,

                    'warehouse_name' => $item['warehouse_name'] ?? null,
                    'country_name' => $item['country_name'] ?? null,
                    'oblast_okrug_name' => $item['oblast_okrug_name'] ?? null,
                    'region_name' => $item['region_name'] ?? null,

                    'income_id' => $item['income_id'] ?? null,
                    'sale_id' => $item['sale_id'] ?? null,
                    'odid' => $item['odid'] ?? null,

                    'spp' => $item['spp'] ?? null,
                    'for_pay' => $item['for_pay'] ?? null,
                    'finished_price' => $item['finished_price'] ?? null,
                    'price_with_disc' => $item['price_with_disc'] ?? null,

                    'nm_id' => $item['nm_id'] ?? null,

                    'subject' => $item['subject'] ?? null,
                    'category' => $item['category'] ?? null,
                    'brand' => $item['brand'] ?? null,

                    'is_storno' => $item['is_storno'] ?? null,

                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $totalApi += count($items);

            // 🔥 ВАЖНО: без ignore, без upsert
            foreach (array_chunk($rows, 200) as $chunk) {
                DB::table('sales')->insert($chunk);
                $totalDb += count($chunk);
            }

            unset($rows, $items);
            gc_collect_cycles();

            $this->info("Page $page OK");

            $page++;
            sleep(2);

        } while ($page <= $lastPage);

        $this->info("DONE");
        $this->info("API: $totalApi");
        $this->info("DB: $totalDb");
    }
}
