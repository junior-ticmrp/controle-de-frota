<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LegacyPreviewCommand extends Command
{
    protected $signature = 'legacy:preview {--source=/srv/frota/storage/app/legacy-source : Diretório protegido dos XML legados} {--json : Exibe o resultado em JSON}';
    protected $description = 'Valida os XML legados e exibe contagens e exceções sem gravar dados.';

    public function handle(): int
    {
        $directory = rtrim((string) $this->option('source'), '/');
        $sources = ['Vereadores.xml' => 'Vereadores', 'requisicoes.xml' => 'requisicoes', 'valorcomb.xml' => 'valorcomb', 'Usuario.xml' => 'Usuario'];
        $xml = [];
        foreach ($sources as $file => $_) {
            $path = $directory.'/'.$file;
            if (! is_readable($path)) {
                $this->error("Arquivo não legível: {$path}");
                return self::FAILURE;
            }
            $xml[$file] = file_get_contents($path);
            if ($xml[$file] === false) {
                $this->error("Falha ao ler: {$path}");
                return self::FAILURE;
            }
        }

        $requests = $this->records($xml['requisicoes.xml'], 'requisicoes');
        $fuelings = $this->records($xml['Vereadores.xml'], 'Vereadores');
        $prices = $this->records($xml['valorcomb.xml'], 'valorcomb');
        $users = $this->records($xml['Usuario.xml'], 'Usuario');

        $requestCodes = [];
        $duplicateRequestCodes = 0;
        foreach ($requests as $row) {
            $code = $this->integer($row['req_codigo'] ?? null);
            if ($code <= 0) continue;
            if (isset($requestCodes[$code])) $duplicateRequestCodes++;
            $requestCodes[$code] = true;
        }
        $fuelingCodes = [];
        $missingRequests = 0;
        $unreliableDates = 0;
        foreach ($fuelings as $index => $row) {
            $code = $this->integer($row['Código'] ?? null);
            if ($code > 0 && isset($fuelingCodes[$code])) $duplicateFuelingCodes = ($duplicateFuelingCodes ?? 0) + 1;
            if ($code > 0) $fuelingCodes[$code] = true;
            $request = $this->integer($row['Requerimento'] ?? null);
            if ($request > 0 && ! isset($requestCodes[$request])) $missingRequests++;
            if ($this->legacyDateIsUnreliable($row['Data'] ?? null, $row['DataSerial'] ?? null)) $unreliableDates++;
        }
        $duplicateFuelingCodes = $duplicateFuelingCodes ?? 0;
        $duplicatePriceCodes = $this->duplicateCount($prices, 'Código');

        $payload = [
            'mode' => 'read-only preview',
            'source_directory' => $directory,
            'source_counts' => ['Vereadores' => count($fuelings), 'requisicoes' => count($requests), 'valorcomb' => count($prices), 'Usuario' => count($users)],
            'expected_counts' => ['Vereadores' => 7193, 'requisicoes' => 7493, 'valorcomb' => 2953, 'Usuario' => 9],
            'exceptions' => ['duplicate_request_codes' => $duplicateRequestCodes, 'duplicate_fueling_codes' => $duplicateFuelingCodes, 'duplicate_price_codes' => $duplicatePriceCodes, 'fuelings_with_missing_explicit_request' => $missingRequests, 'fuelings_with_unreliable_date' => $unreliableDates],
            'target_counts_before_import' => $this->targetCounts(),
            'safeguards' => ['no write transaction was started', 'legacy passwords are not read as credentials', 'current users are not modified'],
        ];

        if ($this->option('json')) { $this->line(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); return self::SUCCESS; }
        $this->info('PRÉVIA SOMENTE-LEITURA — nenhum dado foi gravado.');
        $this->table(['Fonte', 'XML', 'Esperado'], collect($payload['source_counts'])->map(fn($count, $name) => [$name, $count, $payload['expected_counts'][$name]])->values()->all());
        $this->newLine();
        $this->table(['Exceção', 'Quantidade'], collect($payload['exceptions'])->map(fn($count, $name) => [$name, $count])->values()->all());
        $this->newLine();
        $this->table(['Tabela destino', 'Registros atuais'], collect($payload['target_counts_before_import'])->map(fn($count, $name) => [$name, $count])->values()->all());
        $this->warn('Revise esta saída antes de executar legacy:import.');
        return self::SUCCESS;
    }

    private function records(string $xml, string $tag): array
    {
        preg_match_all('/<'.preg_quote($tag, '/').'>(.*?)<\/'.preg_quote($tag, '/').'>/su', $xml, $matches);
        return array_map(function (string $entry): array {
            preg_match_all('/<([^\/\s>]+)>(.*?)<\/\1>/su', $entry, $fields, PREG_SET_ORDER);
            $row = [];
            foreach ($fields as $field) $row[$field[1]] = $this->clean($field[2]);
            return $row;
        }, $matches[1] ?? []);
    }
    private function clean(string $value): string { return trim(preg_replace('/\s+/u', ' ', html_entity_decode(preg_replace('/<!\[CDATA\[(.*?)\]\]>/su', '$1', $value), ENT_QUOTES | ENT_XML1, 'UTF-8')) ?? ''); }
    private function integer(?string $value): int { return (int) preg_replace('/[^0-9-]/', '', $this->clean((string) $value)); }
    private function duplicateCount(array $rows, string $field): int { $seen=[]; $duplicates=0; foreach($rows as $row){$key=$this->integer($row[$field]??null); if($key<=0)continue; if(isset($seen[$key]))$duplicates++; $seen[$key]=true;} return $duplicates; }
    private function legacyDateIsUnreliable(?string $date, ?string $serial): bool { $text=$this->clean((string) $date); $timestamp=strtotime($text); if($timestamp!==false && (int) date('Y',$timestamp)>=2000 && (int) date('Y',$timestamp)<=2100) return false; $serialNumber=$this->integer($serial); return !($serialNumber>=36526 && $serialNumber<=73050); }
    private function targetCounts(): array { return collect(['people','fuel_types','vehicles','valorcomb','fuel_requests','fuelings','maintenance_records','legacy_import_records'])->mapWithKeys(fn($table)=>[$table=>DB::table($table)->count()])->all(); }
}
