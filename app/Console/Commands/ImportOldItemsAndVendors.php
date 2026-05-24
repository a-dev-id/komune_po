<?php

namespace App\Console\Commands;

use App\Models\Item;
use App\Models\Vendor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportOldItemsAndVendors extends Command
{
    protected $signature = 'purchasing-lite:import-old-items-vendors
                            {path=storage/app/imports/items_and_vendors.sql : SQL file path}';

    protected $description = 'Import only old items and vendors from a SQL dump file';

    public function handle(): int
    {
        $path = base_path($this->argument('path'));

        if (! file_exists($path)) {
            $this->error("SQL file not found:");
            $this->line($path);
            $this->newLine();
            $this->line("Put your SQL file here:");
            $this->line(base_path('storage/app/imports/items_and_vendors.sql'));

            return self::FAILURE;
        }

        $this->info('Reading SQL file...');
        $sql = file_get_contents($path);

        DB::beginTransaction();

        try {
            $itemsImported = $this->importItems($sql);
            $vendorsImported = $this->importVendors($sql);

            DB::commit();

            $this->info('Import completed.');
            $this->line("Items imported/updated: {$itemsImported}");
            $this->line("Vendors imported/updated: {$vendorsImported}");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            DB::rollBack();

            $this->error('Import failed.');
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }

    private function importItems(string $sql): int
    {
        $count = 0;

        foreach ($this->extractInsertBlocks($sql, 'items') as $block) {
            $columns = $this->parseColumns($block['columns']);
            $rows = $this->parseRows($block['values']);

            foreach ($rows as $row) {
                $data = $this->combineColumnsAndValues($columns, $row);

                $name = trim((string) ($data['name'] ?? ''));

                if ($name === '') {
                    continue;
                }

                $sku = $this->cleanValue($data['sku'] ?? null);

                $payload = [
                    'name' => $name,
                    'sku' => $sku,
                    'category' => $this->cleanValue($data['category'] ?? null),
                    'brand' => $this->cleanValue($data['brand'] ?? null),
                    'default_unit' => $this->cleanValue($data['default_unit'] ?? null),
                    'default_specification' => $this->cleanValue($data['default_specification'] ?? null),
                    'last_price' => $this->cleanDecimal($data['last_price'] ?? null),
                    'currency' => $this->cleanValue($data['currency'] ?? 'IDR') ?: 'IDR',
                    'is_active' => (bool) ((int) ($data['is_active'] ?? 1)),
                ];

                if (! empty($sku)) {
                    Item::updateOrCreate(
                        ['sku' => $sku],
                        $payload
                    );
                } else {
                    Item::updateOrCreate(
                        ['name' => $name],
                        $payload
                    );
                }

                $count++;
            }
        }

        return $count;
    }

    private function importVendors(string $sql): int
    {
        $count = 0;

        foreach ($this->extractInsertBlocks($sql, 'vendors') as $block) {
            $columns = $this->parseColumns($block['columns']);
            $rows = $this->parseRows($block['values']);

            foreach ($rows as $row) {
                $data = $this->combineColumnsAndValues($columns, $row);

                $name = trim((string) ($data['name'] ?? ''));

                if ($name === '') {
                    continue;
                }

                $normalizedName = Vendor::normalizeName($name);

                Vendor::updateOrCreate(
                    ['normalized_name' => $normalizedName],
                    [
                        'name' => $name,
                        'category' => $this->cleanValue($data['category'] ?? null),
                        'contact_person' => $this->cleanValue($data['contact_person'] ?? null),
                        'phone' => $this->cleanValue($data['phone'] ?? null),
                        'email' => $this->cleanValue($data['email'] ?? null),
                        'address' => $this->cleanValue($data['address'] ?? null),
                        'notes' => $this->cleanValue($data['notes'] ?? null),
                        'is_active' => (bool) ((int) ($data['is_active'] ?? 1)),
                    ]
                );

                $count++;
            }
        }

        return $count;
    }

    private function extractInsertBlocks(string $sql, string $table): array
    {
        $pattern = '/INSERT\s+INTO\s+`?' . preg_quote($table, '/') . '`?\s*\((.*?)\)\s*VALUES\s*(.*?);/is';

        preg_match_all($pattern, $sql, $matches, PREG_SET_ORDER);

        $blocks = [];

        foreach ($matches as $match) {
            $blocks[] = [
                'columns' => $match[1],
                'values' => $match[2],
            ];
        }

        return $blocks;
    }

    private function parseColumns(string $columns): array
    {
        return array_map(function ($column) {
            return trim(str_replace('`', '', $column));
        }, explode(',', $columns));
    }

    private function parseRows(string $values): array
    {
        $rows = [];
        $current = '';
        $depth = 0;
        $inString = false;
        $length = strlen($values);

        for ($i = 0; $i < $length; $i++) {
            $char = $values[$i];
            $previous = $i > 0 ? $values[$i - 1] : null;

            if ($char === "'" && $previous !== '\\') {
                $inString = ! $inString;
            }

            if (! $inString && $char === '(') {
                if ($depth === 0) {
                    $current = '';
                    $depth++;
                    continue;
                }

                $depth++;
            }

            if (! $inString && $char === ')') {
                $depth--;

                if ($depth === 0) {
                    $rows[] = $this->parseRowValues($current);
                    $current = '';
                    continue;
                }
            }

            if ($depth > 0) {
                $current .= $char;
            }
        }

        return $rows;
    }

    private function parseRowValues(string $row): array
    {
        return str_getcsv($row, ',', "'", '\\');
    }

    private function combineColumnsAndValues(array $columns, array $values): array
    {
        $data = [];

        foreach ($columns as $index => $column) {
            $data[$column] = $values[$index] ?? null;
        }

        return $data;
    }

    private function cleanValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '' || strtoupper($value) === 'NULL') {
            return null;
        }

        return stripslashes($value);
    }

    private function cleanDecimal(mixed $value): ?float
    {
        $value = $this->cleanValue($value);

        if ($value === null) {
            return null;
        }

        return (float) $value;
    }
}
