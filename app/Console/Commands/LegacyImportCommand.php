<?php

namespace App\Console\Commands;

use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

class LegacyImportCommand extends Command
{
    protected $signature = 'legacy:import {--source=/srv/frota/storage/app/legacy-source : Diretório protegido dos XML legados} {--confirm : Confirma a importação atômica em banco operacional vazio}';
    protected $description = 'Importa registros XML legados de frota em transação única, preservando chaves e auditoria.';

    public function handle(): int
    {
        if (! $this->option('confirm')) { $this->error('Use --confirm após revisar legacy:preview.'); return self::FAILURE; }
        $source = $this->loadSources(rtrim((string) $this->option('source'), '/'));
        $this->ensureTargetSchema();
        $this->ensureTargetIsEmpty();

        try {
            $result = DB::transaction(function () use ($source): array { return $this->import($source); }, 3);
        } catch (Throwable $exception) {
            $this->error('IMPORTAÇÃO REVERTIDA: '.$exception->getMessage());
            return self::FAILURE;
        }

        $this->info('IMPORTAÇÃO TRANSACIONAL CONCLUÍDA.');
        $this->table(['Indicador', 'Resultado'], collect($result['counts'])->map(fn ($value, $key) => [$key, $value])->values()->all());
        $this->newLine();
        $this->table(['Exceção preservada', 'Quantidade'], collect($result['exceptions'])->map(fn ($value, $key) => [$key, $value])->values()->all());
        $this->warn('Senhas legadas não foram importadas e usuários atuais não foram modificados.');
        return self::SUCCESS;
    }

    private function loadSources(string $directory): array
    {
        $files = ['Vereadores.xml' => 'Vereadores', 'requisicoes.xml' => 'requisicoes', 'valorcomb.xml' => 'valorcomb', 'Usuario.xml' => 'Usuario'];
        $result = [];
        foreach ($files as $file => $tag) {
            $path = $directory.'/'.$file;
            if (! is_readable($path)) throw new RuntimeException("Arquivo de origem não legível: {$path}");
            $content = file_get_contents($path);
            if ($content === false) throw new RuntimeException("Falha de leitura: {$path}");
            $result[$tag] = $this->records($content, $tag);
        }
        foreach (['Vereadores' => 7193, 'requisicoes' => 7493, 'valorcomb' => 2953, 'Usuario' => 9] as $name => $expected) {
            if (count($result[$name]) !== $expected) throw new RuntimeException("Contagem inesperada em {$name}: ".count($result[$name])."; esperado {$expected}.");
        }
        return $result;
    }

    private function ensureTargetIsEmpty(): void
    {
        foreach (['people','fuel_types','vehicles','valorcomb','fuel_requests','fuelings','maintenance_records','legacy_import_records'] as $table) {
            if (DB::table($table)->exists()) throw new RuntimeException("Importação bloqueada: {$table} já possui registros.");
        }
    }

    private function ensureTargetSchema(): void
    {
        $required = [
            'people' => ['full_name','role','active','created_at','updated_at'],
            'fuel_types' => ['name','abbreviation','active','created_at','updated_at'],
            'vehicles' => ['plate','model','fuel_type_id','current_odometer','status','maintenance_interval_km','maintenance_interval_days','legacy_plate','created_at','updated_at'],
            'valorcomb' => ['fuel_type_id','effective_at','valor_bruto','desconto','valorcomb','source','legacy_codigo','created_at'],
            'fuel_requests' => ['request_number','requested_at','vehicle_id','fuel_type_id','odometer','status','legacy_codigo','created_at','updated_at'],
            'fuelings' => ['fueling_at','request_id','vehicle_id','fuel_type_id','odometer','liters','unit_price','total_amount','legacy_codigo','legacy_source_key','legacy_date_unreliable','created_at','updated_at'],
            'legacy_import_records' => ['legacy_table','legacy_key','target_table','target_id','status','message','source_payload','imported_at'],
        ];
        foreach ($required as $table => $columns) {
            $missing = array_values(array_diff($columns, Schema::getColumnListing($table)));
            if ($missing !== []) throw new RuntimeException("Esquema incompatível em {$table}; colunas ausentes: ".implode(', ', $missing));
        }
    }

    private function import(array $source): array
    {
        $now = now();
        $fuels = [];
        foreach (array_merge($source['valorcomb'], $source['requisicoes'], $source['Vereadores']) as $row) $this->rememberFuel($fuels, $row['Combustivel'] ?? $row['req_combustivel'] ?? null);
        $this->batchInsert('fuel_types', array_map(fn ($name) => ['name'=>$name,'abbreviation'=>$this->abbreviation($name),'active'=>true,'created_at'=>$now,'updated_at'=>$now], array_values($fuels)));
        $fuelIds = DB::table('fuel_types')->get(['id','name'])->mapWithKeys(fn ($row) => [$this->key($row->name) => $row->id])->all();

        $people = [];
        foreach ($source['requisicoes'] as $row) { $this->rememberPerson($people, $row['req_vereador'] ?? null, $this->requesterRole($row['req_vereador'] ?? '')); $this->rememberPerson($people, $row['req_motorista'] ?? null, 'driver'); }
        foreach ($source['Vereadores'] as $row) $this->rememberPerson($people, $row['Vereador'] ?? null, $this->requesterRole($row['Vereador'] ?? ''));
        $this->batchInsert('people', array_map(fn ($person) => ['full_name'=>$person['name'],'role'=>$person['role'],'active'=>true,'created_at'=>$now,'updated_at'=>$now], array_values($people)));
        $personIds = DB::table('people')->get(['id','full_name','role'])->mapWithKeys(fn ($row) => [$row->role.':'.$this->key($row->full_name) => $row->id])->all();

        $vehicles = [];
        foreach ($source['requisicoes'] as $row) $this->rememberVehicle($vehicles, $row['req_placa'] ?? null, $row['req_veiculo'] ?? null, $row['req_combustivel'] ?? null, $row['req_odometro'] ?? null, $fuelIds);
        foreach ($source['Vereadores'] as $row) $this->rememberVehicle($vehicles, $row['placa'] ?? null, $row['veiculo'] ?? null, $row['Combustivel'] ?? null, $row['odometro'] ?? null, $fuelIds);
        $this->batchInsert('vehicles', array_map(fn ($row) => ['plate'=>$row['plate'],'model'=>$row['model'],'fuel_type_id'=>$row['fuel_type_id'],'current_odometer'=>$row['odometer'],'status'=>'active','maintenance_interval_km'=>10000,'maintenance_interval_days'=>180,'legacy_plate'=>$row['legacy_plate'],'created_at'=>$now,'updated_at'=>$now], array_values($vehicles)));
        $vehicleIds = DB::table('vehicles')->pluck('id','plate')->all();

        $requestRows = []; $requestNumbers = []; $audits = []; $warnings = 0; $maxRequestNumber = 0;
        foreach ($source['requisicoes'] as $row) {
            $number=$this->integer($row['req_codigo'] ?? null); $plate=$this->plate($row['req_placa'] ?? null); $vehicleId=$vehicleIds[$plate] ?? null;
            if ($number<=0 || ! $vehicleId) { $audits[]=$this->audit('requisicoes',(string)($row['req_codigo'] ?? 'sem-codigo'),'fuel_requests',null,'warning','Requisição sem número ou placa válida; não criada.',$row); $warnings++; continue; }
            [$requestedAt,$unreliable]=$this->dateOf($row['req_data_emissao'] ?? null, $row['DataSerial'] ?? null); if($unreliable)$warnings++;
            $requestRows[]=['request_number'=>$number,'requested_at'=>$requestedAt,'authorization_at'=>$requestedAt,'authorization_expires_at'=>(clone $requestedAt)->addDays(3),'vehicle_id'=>$vehicleId,'requester_person_id'=>$personIds[$this->requesterRole($row['req_vereador'] ?? '').':'.$this->key($row['req_vereador'] ?? '')] ?? null,'driver_person_id'=>$personIds['driver:'.$this->key($row['req_motorista'] ?? '')] ?? null,'fuel_type_id'=>$this->fuelId($fuelIds,$row['req_combustivel'] ?? null),'odometer'=>max(0,$this->integer($row['req_odometro'] ?? null)),'requested_liters'=>($this->clean($row['req_litros'] ?? '')!=='' ? $this->decimal($row['req_litros'] ?? null,3) : null),'estimated_amount'=>($this->clean($row['req_valor'] ?? '')!=='' ? $this->decimal($row['req_valor'] ?? null,2) : null),'status'=>'fulfilled','legacy_codigo'=>$number,'created_at'=>$now,'updated_at'=>$now];
            $requestNumbers[$number]=true; $maxRequestNumber=max($maxRequestNumber,$number); $audits[]=$this->audit('requisicoes',(string)$number,'fuel_requests',null,$unreliable?'warning':'imported',$unreliable?'Data técnica utilizada para revisão.':null,$row);
        }
        $this->batchInsert('fuel_requests',$requestRows);
        $requestIds=DB::table('fuel_requests')->pluck('id','request_number')->all();

        $complementary=0; $complementaryMissing=0; $complementaryDuplicates=0; $nextComplementaryNumber=990000000; $fuelingRows=[]; $fuelingAudits=[]; $unreliableFuelingDates=0; $fulfilledRequestNumbers=[];
        foreach ($source['Vereadores'] as $index=>$row) {
            $code=$this->integer($row['Código'] ?? null); $plate=$this->plate($row['placa'] ?? null); $vehicleId=$vehicleIds[$plate] ?? null; $requestNumber=$this->integer($row['Requerimento'] ?? null);
            if ($code<=0 || ! $vehicleId) { $fuelingAudits[]=$this->audit('Vereadores','Vereadores:'.$code.':'.$index,'fuelings',null,'warning','Abastecimento sem chave ou placa vinculável; não criado.',$row); $warnings++; continue; }
            $hasOriginalRequest = $requestNumber > 0 && isset($requestIds[$requestNumber]);
            if (! $hasOriginalRequest || isset($fulfilledRequestNumbers[$requestNumber])) {
                [$technicalDate] = $this->dateOf($row['Data'] ?? null,$row['DataSerial'] ?? null);
                while (isset($requestIds[$nextComplementaryNumber])) $nextComplementaryNumber++;
                $technicalNumber = $nextComplementaryNumber++;
                $reason = ! $hasOriginalRequest
                    ? 'Requisição histórica complementar criada porque a requisição indicada no legado não foi localizada.'
                    : 'Requisição histórica complementar criada porque a requisição legada possui mais de um abastecimento.';
                DB::table('fuel_requests')->insert(['request_number'=>$technicalNumber,'requested_at'=>$technicalDate,'authorization_at'=>$technicalDate,'authorization_expires_at'=>(clone $technicalDate)->addDays(3),'vehicle_id'=>$vehicleId,'fuel_type_id'=>$this->fuelId($fuelIds,$row['Combustivel'] ?? null),'odometer'=>max(0,$this->integer($row['odometro'] ?? null)),'status'=>'fulfilled','legacy_codigo'=>$technicalNumber,'notes'=>$reason,'created_at'=>$now,'updated_at'=>$now]);
                $requestIds[$technicalNumber]=DB::getPdo()->lastInsertId();
                $sourceKey='Vereadores:'.$code.':'.$index;
                $audits[]=$this->audit('Vereadores','complement:'.$sourceKey,'fuel_requests',(int)$requestIds[$technicalNumber],'warning',$reason,$row);
                $requestNumber=$technicalNumber; $complementary++;
                if ($hasOriginalRequest) $complementaryDuplicates++; else $complementaryMissing++;
            }
            $fulfilledRequestNumbers[$requestNumber] = true;
            [$fuelingAt,$unreliable]=$this->dateOf($row['Data'] ?? null,$row['DataSerial'] ?? null); if($unreliable)$unreliableFuelingDates++;
            $liters=max(0,$this->money($row['Litros'] ?? null)); $unit=$this->money($row['ValorPago'] ?? null); if($unit<=0 && $liters>0)$unit=$this->money($row['ValorGasto'] ?? null)/$liters; $total=$this->money($row['ValorGasto'] ?? null); if($total<=0)$total=$liters*$unit; $sourceKey='Vereadores:'.$code.':'.$index;
            $fuelingRows[]=['fueling_at'=>$fuelingAt,'request_id'=>$requestIds[$requestNumber],'vehicle_id'=>$vehicleId,'fuel_type_id'=>$this->fuelId($fuelIds,$row['Combustivel'] ?? null),'odometer'=>max(0,$this->integer($row['odometro'] ?? null)),'liters'=>$this->number($liters,3),'unit_price'=>$this->number($unit,3),'total_amount'=>$this->number($total,2),'legacy_codigo'=>$code,'legacy_source_key'=>$sourceKey,'legacy_date_unreliable'=>$unreliable,'created_at'=>$now,'updated_at'=>$now];
            $fuelingAudits[]=$this->audit('Vereadores',$sourceKey,'fuelings',null,$unreliable?'warning':'imported',$unreliable?'Data legada inválida; importada com data técnica e excluída de análises temporais.':null,$row);
        }
        $this->batchInsert('fuelings',$fuelingRows);

        $priceRows=[]; foreach($source['valorcomb'] as $row){ [$date,$unreliable]=$this->dateOf($row['Dia'] ?? null,$row['DiaSerial'] ?? null); if($unreliable)$warnings++; $code=$this->integer($row['Código'] ?? null); $gross=$this->money($row['Valor'] ?? null); $discount=$this->money($row['Desconto'] ?? null); $net=$this->money($row['ValorDescontado'] ?? null); if($net<=0)$net=max(0,$gross-$discount); $priceRows[]=['fuel_type_id'=>$this->fuelId($fuelIds,$row['Combustivel'] ?? null),'effective_at'=>$date,'valor_bruto'=>$this->number($gross,3),'desconto'=>$this->number($discount,4),'valorcomb'=>$this->number($net,3),'source'=>$this->clean($row['Origem'] ?? '') ?: null,'legacy_codigo'=>$code?:null,'created_at'=>$now]; $audits[]=$this->audit('valorcomb',(string)($code?:($row['DiaSerial'] ?? '').'-'.($row['Combustivel'] ?? '')),'valorcomb',null,$unreliable?'warning':'imported',$unreliable?'Data técnica utilizada para revisão.':null,$row); }
        $this->batchInsert('valorcomb',$priceRows);

        $fuelingIds=DB::table('fuelings')->whereNotNull('legacy_source_key')->pluck('id','legacy_source_key')->all(); $requestTargetIds=DB::table('fuel_requests')->whereNotNull('legacy_codigo')->pluck('id','legacy_codigo')->all(); $priceTargetIds=DB::table('valorcomb')->whereNotNull('legacy_codigo')->pluck('id','legacy_codigo')->all();
        foreach($audits as &$audit){ if($audit['target_table']==='fuel_requests')$audit['target_id']=$requestTargetIds[$this->integer($audit['legacy_key'])]??null; if($audit['target_table']==='valorcomb')$audit['target_id']=$priceTargetIds[$this->integer($audit['legacy_key'])]??null; }
        foreach($fuelingAudits as &$audit)$audit['target_id']=$fuelingIds[$audit['legacy_key']]??null;
        foreach($source['Usuario'] as $row){ $login=$this->clean($row['login'] ?? '') ?: 'conta-sem-login'; $audits[]=$this->audit('Usuario',$login,null,null,'skipped','Conta legada registrada para auditoria; senha não foi migrada.',['login'=>$login,'grupo'=>$row['grupo'] ?? null]); }
        $this->batchInsert('legacy_import_records',array_merge($audits,$fuelingAudits));
        DB::table('document_sequences')->where('name','fuel_request')->update(['last_value'=>$maxRequestNumber,'updated_at'=>$now]);

        return ['counts'=>['people'=>DB::table('people')->count(),'fuel_types'=>DB::table('fuel_types')->count(),'vehicles'=>DB::table('vehicles')->count(),'valorcomb'=>DB::table('valorcomb')->count(),'fuel_requests'=>DB::table('fuel_requests')->count(),'fuelings'=>DB::table('fuelings')->count(),'legacy_import_records'=>DB::table('legacy_import_records')->count()],'exceptions'=>['requisições históricas complementares'=>$complementary,'complementos por requisição ausente'=>$complementaryMissing,'complementos por abastecimento adicional'=>$complementaryDuplicates,'abastecimentos com data não confiável'=>$unreliableFuelingDates,'avisos de importação'=>$warnings,'senhas importadas'=>0]];
    }

    private function records(string $xml,string $tag): array { preg_match_all('/<'.preg_quote($tag,'/').'>(.*?)<\/'.preg_quote($tag,'/').'>/su',$xml,$matches); return array_map(function(string $entry):array{preg_match_all('/<([^\/\s>]+)>(.*?)<\/\1>/su',$entry,$fields,PREG_SET_ORDER);$row=[];foreach($fields as $field)$row[$field[1]]=$this->clean($field[2]);return $row;},$matches[1]??[]); }
    private function clean(?string $value): string { return trim(preg_replace('/\s+/u',' ',html_entity_decode(preg_replace('/<!\[CDATA\[(.*?)\]\]>/su','$1',(string)$value),ENT_QUOTES|ENT_XML1,'UTF-8'))??''); }
    private function key(?string $value): string { $text=$this->clean($value); $text=iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$text)?:$text; return strtoupper(preg_replace('/\s+/',' ',$text)); }
    private function plate(?string $value): string { return substr(preg_replace('/[^A-Z0-9]/','',$this->key($value)),0,10); }
    private function integer(?string $value): int { return (int)preg_replace('/[^0-9-]/','',$this->clean($value)); }
    private function money(?string $value): float { $raw=$this->clean($value); if($raw==='')return 0; if(str_contains($raw,',')&&str_contains($raw,'.'))$raw=str_replace('.','',$raw); $raw=str_replace(',','.',$raw); return is_numeric($raw)?(float)$raw:0; }
    private function decimal(?string $value,int $scale): string { return $this->number($this->money($value),$scale); }
    private function number(float $value,int $scale): string { return number_format(max(0,$value),$scale,'.',''); }
    private function abbreviation(string $name): string { $key=$this->key($name); return str_contains($key,'ETANOL')?'ETN':(str_contains($key,'DIESEL')?'DSL':(str_contains($key,'GASOL')?'GAS':(str_contains($key,'GNV')?'GNV':substr($key,0,12)))); }
    private function requesterRole(string $name): string { return str_contains($this->key($name),'ADMINISTR')?'administrator':'council_member'; }
    private function rememberFuel(array &$fuels,?string $name): void { $label=$this->clean($name)?:'Não informado'; $fuels[$this->key($label)]=$label; }
    private function rememberPerson(array &$people,?string $name,string $role): void { $label=$this->clean($name)?:'Não informado'; $people[$role.':'.$this->key($label)]=['name'=>substr($label,0,180),'role'=>$role]; }
    private function rememberVehicle(array &$vehicles,?string $rawPlate,?string $model,?string $fuel,?string $odometer,array $fuelIds): void { $plate=$this->plate($rawPlate); if($plate==='')return; $candidate=['plate'=>$plate,'model'=>substr($this->clean($model)?:'Veículo sem modelo informado',0,120),'fuel_type_id'=>$this->fuelId($fuelIds,$fuel),'odometer'=>max(0,$this->integer($odometer)),'legacy_plate'=>substr($this->clean($rawPlate),0,32)?:null]; if(!isset($vehicles[$plate])||$candidate['odometer']>=$vehicles[$plate]['odometer'])$vehicles[$plate]=$candidate; }
    private function fuelId(array $fuelIds, ?string $fuel): int { $key=$this->key($this->clean($fuel)?:'Não informado'); if(!isset($fuelIds[$key]))throw new RuntimeException("Combustível sem chave normalizada: {$key}"); return (int)$fuelIds[$key]; }
    private function dateOf(?string $date,?string $serial): array { $text=$this->clean($date); if($text!==''){try{$parsed=CarbonImmutable::parse($text);if($parsed->year>=2000&&$parsed->year<=2100)return[$parsed,false];}catch(Throwable){}} $value=$this->integer($serial); if($value>=36526&&$value<=73050)return[CarbonImmutable::create(1899,12,30)->addDays($value),false]; return[CarbonImmutable::create(2000,1,1),true]; }
    private function audit(string $table,string $key,?string $targetTable,?int $targetId,string $status,?string $message,array $payload): array { return ['legacy_table'=>$table,'legacy_key'=>substr($key,0,80),'target_table'=>$targetTable,'target_id'=>$targetId,'status'=>$status,'message'=>$message,'source_payload'=>json_encode($payload,JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE),'imported_at'=>now()]; }
    private function batchInsert(string $table,array $rows): void { foreach(array_chunk($rows,250) as $chunk) if($chunk) DB::table($table)->insert($chunk); }
}
