<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LegacyVehicleReconcileCommand extends Command
{
    protected $signature = 'legacy:reconcile-vehicles {--source=/srv/frota/storage/app/legacy-source : Diretório XML legado}';
    protected $description = 'Compara placas normalizadas nos XML com a tabela vehicles, sem gravar dados.';

    public function handle(): int
    {
        $source = rtrim((string) $this->option('source'), '/');
        $records = array_merge($this->records($source.'/requisicoes.xml', 'requisicoes', 'req_placa'), $this->records($source.'/Vereadores.xml', 'Vereadores', 'placa'));
        $sourcePlates = [];
        foreach ($records as $row) if ($row['plate'] !== '') $sourcePlates[$row['plate']] = $row['raw'];
        $targetPlates = DB::table('vehicles')->pluck('plate')->map(fn ($plate) => $this->plate((string) $plate))->filter()->flip()->all();
        $missing = array_diff_key($sourcePlates, $targetPlates);
        $unexpected = array_diff_key($targetPlates, $sourcePlates);
        $this->info('RECONCILIAÇÃO SOMENTE-LEITURA — nenhum dado foi gravado.');
        $this->table(['Métrica','Quantidade'], [['Placas normalizadas na origem',count($sourcePlates)],['Veículos na tabela destino',count($targetPlates)],['Placas de origem ausentes no destino',count($missing)],['Placas de destino sem referência de origem',count($unexpected)]]);
        if ($missing !== []) $this->table(['Placa normalizada','Exemplo na origem'], collect($missing)->map(fn ($raw,$plate)=>[$plate,$raw])->values()->all());
        if ($unexpected !== []) $this->table(['Placa normalizada','Situação'], collect(array_keys($unexpected))->map(fn ($plate)=>[$plate,'Somente no destino'])->values()->all());
        return self::SUCCESS;
    }

    private function records(string $path, string $tag, string $plateField): array
    {
        $xml = file_get_contents($path);
        if ($xml === false) throw new \RuntimeException("Arquivo não legível: {$path}");
        preg_match_all('/<'.preg_quote($tag,'/').'>(.*?)<\/'.preg_quote($tag,'/').'>/su',$xml,$matches);
        return array_map(function (string $entry) use ($plateField): array {
            preg_match('/<'.preg_quote($plateField,'/').'>(.*?)<\/'.preg_quote($plateField,'/').'>/su',$entry,$field);
            $raw = $this->clean($field[1] ?? '');
            return ['plate'=>$this->plate($raw),'raw'=>$raw];
        }, $matches[1] ?? []);
    }
    private function clean(string $value): string { return trim(preg_replace('/\s+/u',' ',html_entity_decode(preg_replace('/<!\[CDATA\[(.*?)\]\]>/su','$1',$value),ENT_QUOTES|ENT_XML1,'UTF-8'))??''); }
    private function plate(string $value): string { $text=$this->clean($value); $text=iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$text)?:$text; return substr(preg_replace('/[^A-Z0-9]/','',strtoupper($text)),0,10); }
}
