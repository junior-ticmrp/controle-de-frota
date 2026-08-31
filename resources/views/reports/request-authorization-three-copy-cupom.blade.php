<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Autorização - 3 vias Cupom · Requisição #{{ $fuelRequest->request_number }}</title>
    <style>
        @page { size: 80mm 297mm; margin: 3mm; }
        :root { color-scheme: light; }
        * { box-sizing: border-box; }
        html, body { width: 80mm; margin: 0; padding: 0; }
        body {
            width: 74mm;
            color: #000;
            background: #fff;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            line-height: 1.3;
        }
        .screen-actions {
            display: flex;
            gap: 6px;
            width: 74mm;
            margin: 0 0 5mm;
        }
        .screen-actions button {
            padding: 6px 8px;
            border: 1px solid #333;
            border-radius: 4px;
            color: #fff;
            background: #17354f;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
        }
        .screen-actions button:last-child { color: #17354f; background: #fff; }
        .copy {
            width: 74mm;
            padding: 0 0 4mm;
            margin: 0 0 4mm;
            border-bottom: 1px dashed #000;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .copy:last-of-type { border-bottom: 0; margin-bottom: 0; }
        .header {
            display: flex;
            align-items: center;
            gap: 5px;
            padding-bottom: 4px;
            border-bottom: 1px solid #000;
        }
        .header img { width: 18mm; max-height: 10mm; object-fit: contain; }
        .header-text { flex: 1; text-align: center; }
        .institution { font-size: 7px; font-weight: 700; text-transform: uppercase; }
        .document-title { margin-top: 1px; font-size: 10px; font-weight: 800; }
        .copy-label { margin-top: 2px; font-size: 8px; font-weight: 800; text-align: right; text-transform: uppercase; }
        .meta { margin-top: 5px; font-size: 9px; font-weight: 700; text-align: center; }
        .rule { margin: 5px 0; border-top: 1px solid #000; }
        .authorization { margin: 6px 0; padding: 5px 0; text-align: center; }
        .authorization strong { display: block; font-size: 10px; text-transform: uppercase; }
        .authorization span { display: block; margin-top: 2px; font-size: 8px; }
        .validity { margin: 5px 0; padding: 5px; border: 1px solid #000; text-align: center; }
        .validity strong { display: block; font-size: 11px; text-transform: uppercase; }
        .validity span { display: block; margin-top: 2px; font-size: 8px; }
        .data { display: grid; grid-template-columns: 1fr 1fr; gap: 4px 8px; }
        .data div { min-width: 0; }
        .label { display: block; font-size: 7px; font-weight: 800; text-transform: uppercase; }
        .value { display: block; min-height: 12px; font-size: 9px; overflow-wrap: anywhere; }
        .signature { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 13mm; text-align: center; }
        .signature div { padding-top: 3px; border-top: 1px solid #000; font-size: 7px; }
        .footer { margin-top: 5px; font-size: 7px; text-align: center; }
        @media print {
            .screen-actions { display: none !important; }
            body { font-size: 10px; }
        }
    </style>
</head>
<body>
    <div class="screen-actions" aria-label="Ações de impressão">
        <button type="button" onclick="window.print()">Imprimir cupom</button>
        <button type="button" onclick="window.close()">Fechar</button>
        <small>Formato térmico 80 mm · Epson T20</small>
    </div>

    @php($copies = [['VIA POSTO', 'Abastecimento'], ['VIA MOTORISTA', 'Motorista'], ['VIA CMRP', 'Controle interno']])
    @foreach($copies as [$copyLabel, $copyDescription])
        <section class="copy" aria-label="{{ $copyLabel }}">
            <header class="header">
                <img src="{{ asset('images/camara-municipal-ribeirao-preto-logo.png') }}" alt="Câmara Municipal de Ribeirão Preto">
                <div class="header-text">
                    <div class="institution">Câmara Municipal de Ribeirão Preto</div>
                    <div class="document-title">Autorização de combustível</div>
                </div>
            </header>

            <div class="copy-label">{{ $copyLabel }}</div>
            <div class="meta">REQUISIÇÃO #{{ $fuelRequest->request_number }} · {{ $copyDescription }}</div>
            <div class="rule"></div>

            <div class="authorization">
                <strong>Autorização de abastecimento</strong>
                <span>Documento interno vinculado à requisição aprovada.</span>
            </div>

            <div class="data">
                <div>
                    <span class="label">Solicitante responsável</span>
                    <span class="value">{{ $fuelRequest->requester?->full_name ?? 'Não informado' }}</span>
                </div>
                <div>
                    <span class="label">Setor responsável</span>
                    <span class="value">{{ $fuelRequest->responsible_sector ?? 'Não informado' }}</span>
                </div>
                <div>
                    <span class="label">Veículo</span>
                    <span class="value">{{ $fuelRequest->vehicle?->model ?? 'Não informado' }}</span>
                </div>
                <div>
                    <span class="label">Placa</span>
                    <span class="value">{{ $fuelRequest->vehicle?->plate ?? 'Não informada' }}</span>
                </div>
                <div>
                    <span class="label">Motorista</span>
                    <span class="value">{{ $fuelRequest->driver?->full_name ?? 'Não informado' }}</span>
                </div>
                <div>
                    <span class="label">Combustível</span>
                    <span class="value">{{ $fuelRequest->fuelType?->name ?? 'Não informado' }}</span>
                </div>
                <div>
                    <span class="label">Hodômetro</span>
                    <span class="value">{{ number_format((int) $fuelRequest->odometer, 0, ',', '.') }} km</span>
                </div>
                <div>
                    <span class="label">Emitida em</span>
                    <span class="value">{{ $fuelRequest->authorization_at?->format('d/m/Y H:i') ?? '—' }}</span>
                </div>
            </div>

            <div class="validity">
                <strong>Válido por 3 dias</strong>
                <span>Válido até {{ $fuelRequest->authorization_expires_at?->format('d/m/Y H:i') ?? 'Aguardando aprovação' }}</span>
            </div>

            <div class="signature">
                <div>Responsável administrativo<br>{{ $fuelRequest->approver?->name ?? '—' }}</div>
                <div>Conferência / recebimento</div>
            </div>
            <div class="footer">Frota Câmara · Requisição #{{ $fuelRequest->request_number }}</div>
        </section>
    @endforeach

    <script>
        window.addEventListener('load', function () {
            window.setTimeout(function () { window.print(); }, 250);
        });
    </script>
</body>
</html>
