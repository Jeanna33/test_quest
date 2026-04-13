<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WbApiService;
use App\Models\Stock;

class SyncStocks extends Command
{
    protected $signature = 'sync:stocks';
    protected $description = 'Sync stocks from external API';

    public function handle()
    {
        $this->info('Start syncing stocks...');

        $api = new WbApiService();

        $page = 1;
        $lastPage = 1;

        do {
            $this->info("Fetching page: {$page}");

            $response = $api->stocks([
                'page' => $page,
                'limit' => 100,
            ]);

            if (!isset($response['data'])) {
                $this->error('Invalid API response');
                echo print_r($response);
                return 1;
            }

            $items = $response['data'];
            $rows = [];
            foreach ($items as $item) {
                $rows[] = [
                    'date' => $item['date'],
                    'last_change_date' => $item['last_change_date'],
                    'supplier_article' => $item['supplier_article'],
                    'tech_size' => $item['tech_size'],
                    'barcode' => $item['barcode'],
                    'nm_id' => $item['nm_id'],
                    'quantity' => $item['quantity'],
                    'quantity_full' => $item['quantity_full'],
                    'is_supply' => (bool)($item['is_supply'] ?? false),
                    'is_realization' => (bool)($item['is_realization'] ?? false),
                    'warehouse_name' => $item['warehouse_name'],
                    'in_way_to_client' => $item['in_way_to_client'],
                    'in_way_from_client' => $item['in_way_from_client'],
                    'subject' => $item['subject'],
                    'category' => $item['category'],
                    'brand' => $item['brand'],
                    'sc_code' => $item['sc_code'],
                    'price' => $item['price'],
                    'discount' => $item['discount'],
                ];
            }
            Stock::insert($rows);
            $lastPage = $response['meta']['last_page'] ?? 1;
            $page++;

        } while ($page <= $lastPage);

        $this->info('Stocks sync completed successfully!');

        return 0;
    }
}
