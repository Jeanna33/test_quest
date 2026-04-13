<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WbApiService;
use App\Models\Income;


class SyncIncomes extends Command
{
    protected $signature = 'sync:incomes';
    protected $description = 'Sync incomes from external API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $api = new WbApiService();

        $page = 1;
        $lastPage = 1;

        do {
            $this->info("Page: $page");

            $response = $api->incomes([
                'page' => $page,
                'limit' => 100,
            ]);
            if (!isset($response['data'])) {
                $this->error('Invalid API response');
                echo print_r($response);
                return 1;
            }

            $items = $response['data'] ?? [];

            $rows = [];

            foreach ($items as $item) {
                $rows[] = [
                    'income_id' => $item['income_id'],
                    'number' => $item['number'] ?? null,
                    'date' => $item['date'] ?? null,
                    'last_change_date' => $item['last_change_date'] ?? null,
                    'date_close' => $item['date_close'] ?? null,
                    'supplier_article' => $item['supplier_article'] ?? null,
                    'tech_size' => $item['tech_size'] ?? null,
                    'barcode' => $item['barcode'] ?? null,
                    'nm_id' => $item['nm_id'] ?? null,
                    'quantity' => $item['quantity'] ?? 0,
                    'total_price' => $item['total_price'] ?? 0,
                    'warehouse_name' => $item['warehouse_name'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            Income::insert($rows);
            $lastPage = $response['meta']['last_page'] ?? 1;

            $page++;

        } while ($page <= $lastPage);

        $this->info('Incomes sync completed successfully!');

        return 0;
        }
}
