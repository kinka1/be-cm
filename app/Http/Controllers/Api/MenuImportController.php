<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use ZipArchive;

class MenuImportController extends Controller
{
    private const HEADERS = [
        'store_code',
        'category_name',
        'product_name',
        'sku',
        'unit_of_measure',
        'minimum_stock',
        'current_stock',
        'cost_price',
        'selling_price',
        'description',
        'is_active',
    ];

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx', 'max:5120'],
            'store_id' => ['nullable', 'integer', 'exists:stores,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
        ]);

        $rows = $this->rows($request->file('file')->getRealPath(), $request->file('file')->getClientOriginalExtension());

        if ($rows === []) {
            return response()->json(['status' => 'gagal', 'message' => 'file import kosong', 'data' => null], 422);
        }

        $summary = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => []];

        DB::transaction(function () use ($rows, $data, &$summary): void {
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2;

                try {
                    $this->importRow($row, $data, $summary);
                } catch (ValidationException $exception) {
                    $summary['skipped']++;
                    $summary['errors'][] = [
                        'row' => $rowNumber,
                        'sku' => $row['sku'] ?? null,
                        'message' => collect($exception->errors())->flatten()->first(),
                    ];
                }
            }
        });

        return response()->json([
            'status' => 'sukses',
            'message' => $summary['errors'] === [] ? 'import menu selesai' : 'import menu selesai dengan beberapa error',
            'data' => $summary,
        ]);
    }

    public function template()
    {
        $rows = [
            self::HEADERS,
            ['JERUCHA', 'Coffee', 'Americano', 'JERUCHA-MENU-AMERICANO', 'cup', '0', '0', '8000', '18000', 'Kopi hitam', 'true'],
            ['JERUCHA', 'Non Coffee', 'Matcha Latte', 'JERUCHA-MENU-MATCHA-LATTE', 'cup', '0', '0', '10000', '22000', 'Matcha susu', 'true'],
            ['JERUCHA', 'Snack', 'French Fries', 'JERUCHA-MENU-FRENCH-FRIES', 'portion', '0', '0', '9000', '20000', 'Kentang goreng', 'true'],
        ];

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, 'menu-import-template.csv', ['Content-Type' => 'text/csv']);
    }

    public function templateExcel()
    {
        $rows = [
            self::HEADERS,
            ['JERUCHA', 'Coffee', 'Americano', 'JERUCHA-MENU-AMERICANO', 'cup', '0', '0', '8000', '18000', 'Kopi hitam', 'true'],
            ['JERUCHA', 'Non Coffee', 'Matcha Latte', 'JERUCHA-MENU-MATCHA-LATTE', 'cup', '0', '0', '10000', '22000', 'Matcha susu', 'true'],
            ['JERUCHA', 'Snack', 'French Fries', 'JERUCHA-MENU-FRENCH-FRIES', 'portion', '0', '0', '9000', '20000', 'Kentang goreng', 'true'],
        ];

        $path = $this->createXlsxTemplate($rows);

        return response()
            ->download($path, 'menu-import-template.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])
            ->deleteFileAfterSend(true);
    }

    private function importRow(array $row, array $data, array &$summary): void
    {
        $store = $this->resolveStore($row, $data['store_id'] ?? null);
        $category = $this->category($row, $store->id, $data['category_id'] ?? null);

        foreach (['product_name', 'sku', 'unit_of_measure', 'selling_price'] as $field) {
            if (($row[$field] ?? '') === '') {
                throw ValidationException::withMessages([$field => ["{$field} wajib diisi"]]);
            }
        }

        $payload = [
            'store_id' => $store->id,
            'category_id' => $category->id,
            'product_type' => 'menu',
            'product_name' => $row['product_name'],
            'description' => $row['description'] ?? null,
            'unit_of_measure' => $row['unit_of_measure'],
            'minimum_stock' => (float) ($row['minimum_stock'] ?? 0),
            'cost_price' => (float) ($row['cost_price'] ?? 0),
            'selling_price' => (float) $row['selling_price'],
            'is_active' => $this->boolean($row['is_active'] ?? true),
        ];

        $product = Product::query()->where('sku', $row['sku'])->first();
        $wasExisting = $product !== null;

        $product = Product::query()->updateOrCreate(['sku' => $row['sku']], $payload);

        if (!$wasExisting && (float) ($row['current_stock'] ?? 0) !== 0.0) {
            StockTransaction::query()->create([
                'store_id' => $store->id,
                'product_id' => $product->id,
                'transaction_type' => 'in',
                'quantity' => (float) $row['current_stock'],
                'reference_type' => 'adjustment',
                'reference_id' => null,
                'employee_id' => null,
                'notes' => 'Initial stock from menu import',
                'transaction_date' => now(),
                'created_at' => now(),
            ]);
        }

        $summary[$wasExisting ? 'updated' : 'created']++;
    }

    private function resolveStore(array $row, ?int $storeId): Store
    {
        if ($storeId) {
            return Store::query()->findOrFail($storeId);
        }

        if (empty($row['store_code'])) {
            throw ValidationException::withMessages(['store_code' => ['store_code wajib diisi jika store_id tidak dikirim']]);
        }

        $store = Store::query()->where('code', $row['store_code'])->first();

        if (!$store) {
            throw ValidationException::withMessages(['store_code' => ['store_code tidak ditemukan']]);
        }

        return $store;
    }

    private function category(array $row, int $storeId, ?int $categoryId): Category
    {
        if ($categoryId) {
            $category = Category::query()->where('store_id', $storeId)->find($categoryId);

            if (!$category) {
                throw ValidationException::withMessages(['category_id' => ['category_id tidak tersedia pada store ini']]);
            }

            return $category;
        }

        if (empty($row['category_name'])) {
            throw ValidationException::withMessages(['category_name' => ['category_name wajib diisi jika category_id tidak dikirim']]);
        }

        return Category::query()->updateOrCreate(
            ['store_id' => $storeId, 'category_name' => $row['category_name']],
            ['description' => 'Kategori menu']
        );
    }

    private function rows(string $path, string $extension): array
    {
        $rows = strtolower($extension) === 'xlsx'
            ? $this->xlsxRows($path)
            : $this->csvRows($path);

        if (count($rows) < 2) {
            return [];
        }

        $headers = array_map(fn ($header) => strtolower(trim((string) $header)), array_shift($rows));

        return collect($rows)
            ->filter(fn (array $row): bool => collect($row)->filter(fn ($value) => trim((string) $value) !== '')->isNotEmpty())
            ->map(function (array $row) use ($headers): array {
                $assoc = [];

                foreach ($headers as $index => $header) {
                    $assoc[$header] = trim((string) ($row[$index] ?? ''));
                }

                return $assoc;
            })
            ->values()
            ->all();
    }

    private function csvRows(string $path): array
    {
        $handle = fopen($path, 'r');
        $rows = [];

        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = $row;
        }

        fclose($handle);

        return $rows;
    }

    private function createXlsxTemplate(array $rows): string
    {
        $path = tempnam(sys_get_temp_dir(), 'menu-template-');
        $zip = new ZipArchive();

        if ($zip->open($path, ZipArchive::OVERWRITE) !== true) {
            throw ValidationException::withMessages(['file' => ['template excel gagal dibuat']]);
        }

        $zip->addFromString('[Content_Types].xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
    <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
</Types>
XML);

        $zip->addFromString('_rels/.rels', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>
XML);

        $zip->addFromString('xl/workbook.xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <sheets>
        <sheet name="Menus" sheetId="1" r:id="rId1"/>
    </sheets>
</workbook>
XML);

        $zip->addFromString('xl/_rels/workbook.xml.rels', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
</Relationships>
XML);

        $zip->addFromString('xl/worksheets/sheet1.xml', $this->worksheetXml($rows));
        $zip->close();

        return $path;
    }

    private function worksheetXml(array $rows): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>';

        foreach ($rows as $rowIndex => $row) {
            $rowNumber = $rowIndex + 1;
            $xml .= '<row r="'.$rowNumber.'">';

            foreach ($row as $columnIndex => $value) {
                $cell = $this->columnName($columnIndex + 1).$rowNumber;
                $xml .= '<c r="'.$cell.'" t="inlineStr"><is><t>'.$this->xml((string) $value).'</t></is></c>';
            }

            $xml .= '</row>';
        }

        return $xml.'</sheetData></worksheet>';
    }

    private function columnName(int $index): string
    {
        $name = '';

        while ($index > 0) {
            $index--;
            $name = chr(65 + ($index % 26)).$name;
            $index = intdiv($index, 26);
        }

        return $name;
    }

    private function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    private function xlsxRows(string $path): array
    {
        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            throw ValidationException::withMessages(['file' => ['file xlsx tidak bisa dibuka']]);
        }

        $sharedStrings = $this->sharedStrings($zip);
        $sheet = simplexml_load_string($zip->getFromName('xl/worksheets/sheet1.xml'));
        $rows = [];

        foreach ($sheet->sheetData->row as $row) {
            $values = [];

            foreach ($row->c as $cell) {
                $index = $this->columnIndex((string) $cell['r']);
                $values[$index] = $this->cellValue($cell, $sharedStrings);
            }

            ksort($values);
            $rows[] = array_values($values);
        }

        $zip->close();

        return $rows;
    }

    private function cellValue(\SimpleXMLElement $cell, array $sharedStrings): string
    {
        if ((string) $cell['t'] === 'inlineStr') {
            return (string) ($cell->is->t ?? '');
        }

        $value = (string) $cell->v;

        if ((string) $cell['t'] === 's') {
            return $sharedStrings[(int) $value] ?? '';
        }

        return $value;
    }

    private function sharedStrings(ZipArchive $zip): array
    {
        $content = $zip->getFromName('xl/sharedStrings.xml');

        if (!$content) {
            return [];
        }

        $xml = simplexml_load_string($content);
        $strings = [];

        foreach ($xml->si as $item) {
            $strings[] = (string) ($item->t ?? $item->r->t ?? '');
        }

        return $strings;
    }

    private function columnIndex(string $cell): int
    {
        $letters = preg_replace('/\d+/', '', $cell);
        $index = 0;

        foreach (str_split($letters) as $letter) {
            $index = $index * 26 + (ord($letter) - 64);
        }

        return $index - 1;
    }

    private function boolean(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
    }
}
