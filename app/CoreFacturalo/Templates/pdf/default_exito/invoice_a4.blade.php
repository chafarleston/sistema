@php
    use Modules\Template\Helpers\TemplatePdf;
    $establishment = $document->establishment;
    $customer = $document->customer;
    $invoice = $document->invoice;
    $document_base = ($document->note) ? $document->note : null;

    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
    $document_number = $document->series.'-'.str_pad($document->number, 8, '0', STR_PAD_LEFT);
    // $accounts = \App\Models\Tenant\BankAccount::where('show_in_documents', true)->get();
    $accounts = (new TemplatePdf)->getBankAccountsForPdf($document->establishment_id);

    if($document_base) {

        $affected_document_number = ($document_base->affected_document) ? $document_base->affected_document->series.'-'.str_pad($document_base->affected_document->number, 8, '0', STR_PAD_LEFT) : $document_base->data_affected_document->series.'-'.str_pad($document_base->data_affected_document->number, 8, '0', STR_PAD_LEFT);

    } else {

        $affected_document_number = null;
    }

    $payments = $document->payments;

    $document->load('reference_guides');

    $total_payment = $document->payments->sum('payment');
    $balance = ($document->total - $total_payment) - $document->payments->sum('change');

    $logo = "storage/uploads/logos/{$company->logo}";
    if($establishment->logo) {
        $logo = "{$establishment->logo}";
    }

    $configuration_decimal_quantity = App\CoreFacturalo\Helpers\Template\TemplateHelper::getConfigurationDecimalQuantity();

    $facebook_logo = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'default_exito'.DIRECTORY_SEPARATOR.'facebook.png');

    $bnacion_logo = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'default_exito'.DIRECTORY_SEPARATOR.'banknacion_logo');

    $bcp_logo = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'default_exito'.DIRECTORY_SEPARATOR.'bcp_logo');

    $bbva_logo = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'default_exito'.DIRECTORY_SEPARATOR.'bbva_logo');
    
    $scotiabank_logo = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'default_exito'.DIRECTORY_SEPARATOR.'scotiabank_logo');

    $interbank_logo = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'default_exito'.DIRECTORY_SEPARATOR.'interbank_logo');

    $empty_logo = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'default_exito'.DIRECTORY_SEPARATOR.'empty_logo');
    
    $configurationInPdf= App\CoreFacturalo\Helpers\Template\TemplateHelper::getConfigurationInPdf();
@endphp

<html>
<head>
    {{--<title>{{ $document_number }}</title>--}}
    {{--<link href="{{ $path_style }}" rel="stylesheet" />--}}
</head>
<body>
@if($document->state_type->id == '11')
    <div class="company_logo_box" style="position: absolute; text-align: center; top:30%;">
        <img
            src="data:{{mime_content_type(public_path("status_images".DIRECTORY_SEPARATOR."anulado.png"))}};base64, {{base64_encode(file_get_contents(public_path("status_images".DIRECTORY_SEPARATOR."anulado.png")))}}"
            alt="anulado" class="" style="opacity: 0.6;">
    </div>
@endif
<table class="full-width">
    <tr>
        @if($company->logo)
            <td width="20%">
                <div class="company_logo_box">
                    <img
                        src="data:{{mime_content_type(public_path("{$logo}"))}};base64, {{base64_encode(file_get_contents(public_path("{$logo}")))}}"
                        alt="{{$company->name}}" class="company_logo" style="max-width: 130px;">
                </div>
            </td>
        @else
            <td width="20%">
                {{--<img src="{{ asset('logo/logo.jpg') }}" class="company_logo" style="max-width: 150px">--}}
            </td>
        @endif
        <td width="45%" class="pl-3">
            <div class=" text-left">
                <h4 class="font-bold">{{ $company->name }}</h4>
                <h5>{{ 'RUC '.$company->number }}</h5>
                <h6 style="text-transform: uppercase;">
                    {{ ($establishment->address !== '-')? $establishment->address : '' }}
                    {{ ($establishment->district_id !== '-')? ', '.$establishment->district->description : '' }}
                    {{ ($establishment->province_id !== '-')? ', '.$establishment->province->description : '' }}
                    {{ ($establishment->department_id !== '-')? '- '.$establishment->department->description : '' }}
                </h6>
                <h6>{{ ($establishment->telephone !== '-')? 'Central telefónica: '.$establishment->telephone : '' }}</h6>

                <h6>{{ ($establishment->email !== '-')? 'Email: '.$establishment->email : '' }}</h6>

                @isset($establishment->web_address)
                    <h6>{{ ($establishment->web_address !== '-')? 'Web: '.$establishment->web_address : '' }}</h6>
                @endisset

                @isset($establishment->aditional_information)
                    <h6>{{ ($establishment->aditional_information !== '-')? $establishment->aditional_information : '' }}</h6>
                @endisset
            </div>
        </td>
        <td width="33%" class="text-center">
            <table class="border-box-tuexito py-4 px-2 text-center">
            <tr>
                <td class="p-3 font-bold" style="font-size:14px">{{ 'RUC: '.$company->number }}</td>
            </tr>
            <tr style="background-color:lightgray">
                <td class="pt-1 pb-1 pl-4 pr-4 font-bold" style="font-size:16px">{{ $document->document_type->description }}</td>
            </tr>
            <tr>
                <td class="p-3 font-bold" style="font-size:14px">{{ $document_number }}</td>
            </tr>
            </table>
        </td>
    </tr>
</table>
<table class="full-width mt-4">
    <tr>
        <td width="120px">FECHA EMISIÓN</td>
        <td width="8px">:</td>
        <td>{{$document->date_of_issue->format('Y-m-d')}}</td>

        @if($document->detraction)
            <td width="120px">MONEDA</td>
            <td width="8px">:</td>
            <td class="text-uppercase">{{ $document->currency_type->description }}</td>
        @endif
    </tr>
    @if($invoice)
        <tr>
            <td width="140px">FECHA VENCIMIENTO</td>
            <td width="8px">:</td>
            <td>{{$invoice->date_of_due->format('Y-m-d')}}</td>
        </tr>
    @endif

    @if($document->guides)
        @foreach($document->guides as $guide)
                @if(isset($guide->document_type_description))
                    <td style="text-transform: uppercase;">{{ $guide->document_type_description }}</td>
                @else
                    <td>{{ $guide->document_type_id }}</td>
                @endif
                <td>:</td>
                <td>{{ $guide->number }}</td>
        @endforeach
    @endif
    <tr>
        <td style="vertical-align: top;">CLIENTE:</td>
        <td style="vertical-align: top;">:</td>
        <td style="vertical-align: top;">
            {{ $customer->name }}
            @if($customer->internal_code ?? false)
                <br>
                <small>{{ $customer->internal_code ?? '' }}</small>
            @endif
        </td>
    </tr>
    <tr>
        <td>{{ $customer->identity_document_type->description }}</td>
        <td>:</td>
        <td>{{$customer->number}}</td>
    </tr>
    @if ($customer->address !== '')
        <tr>
            <td class="align-top">DIRECCIÓN:</td>
            <td>:</td>
            <td width="310px" style="text-transform: uppercase;">
                {{ $customer->address }}
                {{ ($customer->district_id !== '-')? ', '.$customer->district->description : '' }}
                {{ ($customer->province_id !== '-')? ', '.$customer->province->description : '' }}
                {{ ($customer->department_id !== '-')? '- '.$customer->department->description : '' }}
            </td>
        </tr>
    @endif

    
    @if ($document->reference_data)
        <tr>
            <td width="120px">D. REFERENCIA</td>
            <td width="8px">:</td>
            <td>{{ $document->reference_data}}</td>
        </tr>
    @endif
    @if($document->detraction && $invoice->operation_type_id == '1004')
        <tr>
            <td colspan="4"><strong>DETALLE - SERVICIOS DE TRANSPORTE DE CARGA</strong></td>
        </tr>
        <tr>
            <td class="align-top">Ubigeo origen</td>
            <td>:</td>
            <td>{{ $document->detraction->origin_location_id[2] }}</td>

            <td width="120px">Dirección origen</td>
            <td width="8px">:</td>
            <td>{{ $document->detraction->origin_address }}</td>
        </tr>
        <tr>
            <td class="align-top">Ubigeo destino</td>
            <td>:</td>
            <td>{{ $document->detraction->delivery_location_id[2] }}</td>

            <td width="120px">Dirección destino</td>
            <td width="8px">:</td>
            <td>{{ $document->detraction->delivery_address }}</td>
        </tr>
        <tr>
            <td class="align-top" width="170px">Valor referencial servicio de transporte</td>
            <td>:</td>
            <td>{{ $document->detraction->reference_value_service }}</td>

            <td width="170px">Valor referencia carga efectiva</td>
            <td width="8px">:</td>
            <td>{{ $document->detraction->reference_value_effective_load }}</td>
        </tr>
        <tr>
            <td class="align-top">Valor referencial carga útil</td>
            <td>:</td>
            <td>{{ $document->detraction->reference_value_payload }}</td>

            <td width="120px">Detalle del viaje</td>
            <td width="8px">:</td>
            <td>{{ $document->detraction->trip_detail }}</td>
        </tr>
    @endif

</table>

{{--@if ($document->retention)--}}
{{--    <table class="full-width mt-3">--}}
{{--        <tr>--}}
{{--            <td colspan="3">--}}
{{--                <strong>Información de la retención</strong>--}}
{{--            </td>--}}
{{--        </tr>--}}
{{--        <tr>--}}
{{--            <td width="120px">Base imponible</td>--}}
{{--            <td width="8px">:</td>--}}
{{--            <td>{{ $document->currency_type->symbol}} {{ $document->retention->base }}</td>--}}

{{--            <td width="80px">Porcentaje</td>--}}
{{--            <td width="8px">:</td>--}}
{{--            <td>{{ $document->retention->percentage * 100 }}%</td>--}}
{{--        </tr>--}}
{{--        <tr>--}}
{{--            <td width="120px">Monto</td>--}}
{{--            <td width="8px">:</td>--}}
{{--            <td>{{ $document->currency_type->symbol}} {{ $document->retention->amount }}</td>--}}
{{--        </tr>--}}
{{--    </table>--}}
{{--@endif--}}


@if ($document->isPointSystem())
    <table class="full-width mt-3">
        <tr>
            <td width="120px">P. ACUMULADOS</td>
            <td width="8px">:</td>
            <td>{{ $document->person->accumulated_points }}</td>

            <td width="140px">PUNTOS POR LA COMPRA</td>
            <td width="8px">:</td>
            <td>{{ $document->getPointsBySale() }}</td>
        </tr>
    </table>
@endif


@if ($document->transport)
    <br>
    <strong>Transporte de pasajeros</strong>
    @php
        $transport = $document->transport;
        $origin_district_id = (array)$transport->origin_district_id;
        $destinatation_district_id = (array)$transport->destinatation_district_id;
        $origin_district = Modules\Order\Services\AddressFullService::getDescription($origin_district_id[2]);
        $destinatation_district = Modules\Order\Services\AddressFullService::getDescription($destinatation_district_id[2]);
    @endphp

    <table class="full-width mt-3">
        <tr>
            <td width="120px">{{ $transport->identity_document_type->description }}</td>
            <td width="8px">:</td>
            <td>{{ $transport->number_identity_document }}</td>
            <td width="120px">NOMBRE</td>
            <td width="8px">:</td>
            <td>{{ $transport->passenger_fullname }}</td>
        </tr>
        <tr>
            <td width="120px">N° ASIENTO</td>
            <td width="8px">:</td>
            <td>{{ $transport->seat_number }}</td>
            <td width="120px">M. PASAJERO</td>
            <td width="8px">:</td>
            <td>{{ $transport->passenger_manifest }}</td>
        </tr>
        <tr>
            <td width="120px">F. INICIO</td>
            <td width="8px">:</td>
            <td>{{ $transport->start_date }}</td>
            <td width="120px">H. INICIO</td>
            <td width="8px">:</td>
            <td>{{ $transport->start_time }}</td>
        </tr>
        <tr>
            <td width="120px">U. ORIGEN</td>
            <td width="8px">:</td>
            <td>{{ $origin_district }}</td>
            <td width="120px">D. ORIGEN</td>
            <td width="8px">:</td>
            <td>{{ $transport->origin_address }}</td>
        </tr>
        <tr>
            <td width="120px">U. DESTINO</td>
            <td width="8px">:</td>
            <td>{{ $destinatation_district }}</td>
            <td width="120px">D. DESTINO</td>
            <td width="8px">:</td>
            <td>{{ $transport->destinatation_address }}</td>
        </tr>
    </table>
@endif

@if ($document->dispatch)
    <br/>
    <strong>Guías de remisión</strong>
    <table>
        <tr>
            <td>{{ $document->dispatch->number_full }}</td>
        </tr>
    </table>

@else
    @if($document->reference_guides)
        @if (count($document->reference_guides) > 0)
            <br/>
            <strong>Guías de remisión</strong>
            <table>
                @foreach($document->reference_guides as $guide)
                    <tr>
                        <td>{{ $guide->series }}</td>
                        <td>-</td>
                        <td>{{ $guide->number }}</td>
                    </tr>
                @endforeach
            </table>
        @endif
    @endif
@endif


<table class="full-width mt-3">
    @if ($document->prepayments)
        @foreach($document->prepayments as $p)
            <tr>
                <td width="120px">ANTICIPO</td>
                <td width="8px">:</td>
                <td>{{$p->number}}</td>
            </tr>
        @endforeach
    @endif
    @if ($document->purchase_order)
        <tr>
            <td width="140px">ORDEN DE COMPRA</td>
            <td width="8px">:</td>
            <td>{{ $document->purchase_order }}</td>
        </tr>
    @endif

    
    @if ($document->quotation_id)
        <tr>
            <td width="179px">COTIZACIÓN</td>
            <td width="8px">:</td>
            <td>{{ $document->quotation->identifier }}</td>

            @isset($document->quotation->delivery_date)
                <td width="120px">F. ENTREGA</td>
                <td width="8px">:</td>
                <td>{{ $document->date_of_issue->addDays($document->quotation->delivery_date)->format('d-m-Y') }}</td>
            @endisset
        </tr>

    @endif
    @isset($document->quotation->sale_opportunity)
        <tr>
            <td width="120px">O. VENTA</td>
            <td width="8px">:</td>
            <td>{{ $document->quotation->sale_opportunity->number_full}}</td>
        </tr>
    @endisset
    @if(!is_null($document_base))
        <tr>
            <td width="120px">DOC. AFECTADO</td>
            <td width="8px">:</td>
            <td>{{ $affected_document_number }}</td>
        </tr>
        <tr>
            <td>TIPO DE NOTA</td>
            <td>:</td>
            <td>{{ ($document_base->note_type === 'credit')?$document_base->note_credit_type->description:$document_base->note_debit_type->description}}</td>
        </tr>
        <tr>
            <td>DESCRIPCIÓN</td>
            <td>:</td>
            <td>{{ $document_base->note_description }}</td>
        </tr>
    @endif
    @if($document->folio)
        <tr>
            <td>FOLIO</td>
            <td>:</td>
            <td>{{ $document->folio }}</td>
        </tr>
    @endif
</table>

{{--<table class="full-width mt-3">--}}
{{--<tr>--}}
{{--<td width="25%">Documento Afectado:</td>--}}
{{--<td width="20%">{{ $document_base->affected_document->series }}-{{ $document_base->affected_document->number }}</td>--}}
{{--<td width="15%">Tipo de nota:</td>--}}
{{--<td width="40%">{{ ($document_base->note_type === 'credit')?$document_base->note_credit_type->description:$document_base->note_debit_type->description}}</td>--}}
{{--</tr>--}}
{{--<tr>--}}
{{--<td class="align-top">Descripción:</td>--}}
{{--<td class="text-left" colspan="3">{{ $document_base->note_description }}</td>--}}
{{--</tr>--}}
{{--</table>--}}

<table class="full-width mt-10 mb-10 ">
    <thead class="">
    <tr class="">
        <th class="border-box-tuexito text-center py-2" width="8%" style="background-color:lightgray">CANT.</th>
        <th class="border-box-tuexito text-center py-2" width="9%" style="background-color:lightgray">UNIDAD</th>
        <th class="border-box-tuexito text-center py-2" width="9%" style="background-color:lightgray">CÓDIGO</th>
        <th class="border-box-tuexito text-left py-2" width="47%" style="background-color:lightgray">DESCRIPCIÓN</th>
        <th class="border-box-tuexito text-center py-2" width="10%" style="background-color:lightgray">P.UNIT</th>
        <th class="border-box-tuexito text-center py-2" width="7%" style="background-color:lightgray">DTO.</th>
        <th class="border-box-tuexito text-center py-2" width="10%" style="background-color:lightgray">TOTAL</th>
    </tr>
    </thead>
    <tbody>

          @php
           $height = 170;
          @endphp

         @foreach($document->items as $key => $row)
       <tr>
                <td class="text-center border-left align-top"> 
                    @if(((int)$row->quantity != $row->quantity))
                        {{ $row->quantity }}
                    @else
                        {{ number_format($row->quantity, 0) }}
                    @endif
                </td>
                    <td class="text-center border-left align-top">{{ $row->item->unit_type_id }}</td>
                    <td class="text-center border-left align-top">{{ $row->item->internal_id }}</td>
                    <td class="text-left border-left align-top">{{ $row->item->description }}{!!$row->name_product_pdf!!}
                    
                    @php 
                        $name_product_pdf_length = strlen($row->name_product_pdf); 
                        $height = $height - $name_product_pdf_length;
                    @endphp

                        @if($row->attributes)
                            @foreach($row->attributes as $attr)
                                <br/><span style="font-size: 9px">{!! $attr->description !!} : {{ $attr->value }}</span>
                                @php
                                    if($attr->attribute_type_id !== '5032') {
                                        $height = $height - 15;
                                    }
                                @endphp
                            @endforeach
                        @endif
                        
                        {{--<br/><span style="font-size: 9px">ICBPER : {{ $row->total_plastic_bag_taxes }}</span>--}} </td>
                   

                @if($row->total_isc > 0)
                    <br/><span style="font-size: 9px">ISC : {{ $row->total_isc }} ({{ $row->percentage_isc }}%)</span>
                @endif

                @if (!empty($row->item->presentation)) {!!$row->item->presentation->description!!} @endif

                @if($row->total_plastic_bag_taxes > 0)
                    <br/><span style="font-size: 9px">ICBPER : {{ $row->total_plastic_bag_taxes }}</span>
                @endif

                @if($row->attributes)
                    @foreach($row->attributes as $attr)
                        <br/><span style="font-size: 9px">{!! $attr->description !!} : {{ $attr->value }}</span>
                    @endforeach
                @endif

                @if($row->discounts)
                    @foreach($row->discounts as $dtos)
                        <br/><span style="font-size: 9px">{{ $dtos->factor * 100 }}% {{$dtos->description }}</span>
                    @endforeach
                @endif

                @if($row->charges)
                    @foreach($row->charges as $charge)
                        <br/><span style="font-size: 9px">{{ $document->currency_type->symbol}} {{ $charge->amount}} ({{ $charge->factor * 100 }}%) {{$charge->description }}</span>
                    @endforeach
                @endif

                @if($row->item->is_set == 1)
                    <br>
                    @inject('itemSet', 'App\Services\ItemSetService')
                    @foreach ($itemSet->getItemsSet($row->item_id) as $item)
                        {{$item}}<br>
                    @endforeach
                @endif

                @if($row->item->used_points_for_exchange ?? false)
                    <br>
                    <span
                        style="font-size: 9px">*** Canjeado por {{$row->item->used_points_for_exchange}}  puntos ***</span>
                @endif

                @if($document->has_prepayment)
                    <br>
                    *** Pago Anticipado ***
                @endif
            </td>


            @if ($configuration_decimal_quantity->change_decimal_quantity_unit_price_pdf)
                <td class="text-right border-left align-top">{{ $row->generalApplyNumberFormat($row->unit_price, $configuration_decimal_quantity->decimal_quantity_unit_price_pdf) }}</td>
            @else
                <td class="text-right border-left align-top">{{ number_format($row->unit_price, 2) }}</td>
            @endif

            <td class="text-center border-left align-top">
                @if($row->discounts)
                    @php
                        $total_discount_line = 0;
                        foreach ($row->discounts as $disto) {
                            $total_discount_line = $total_discount_line + $disto->amount;
                        }
                    @endphp
                    {{ number_format($total_discount_line, 2) }}
                @else
                    0
                @endif
            </td>
          
          
                 <td class="p-0 text-right align-top border-left border-right">{{ number_format($row->total, 2) }}</td>
        </tr>

    @php
    $height = $height - 20;
    @endphp

        @endforeach
          @if ($height > 60)
                    <tr>
                    <td class="border-left" style="height: {{ $height }}px"></td>
                    <td class="border-left"></td>
                    <td class="border-left"></td>
                    <td class="border-left"></td>
                    <td class="border-left"></td>
                    <td class="border-left"></td>
                    <td class="border-left border-right"></td>
                    </tr>
            @endif

     <tr>
            {{--<td colspan="9" class="border-box-tuexito"></td>--}}
   
   
            <td colspan="9" class="p-0 border-box-tuexito"></td>
        </tr>

     @if ($document->prepayments)
        @foreach($document->prepayments as $p)
            <tr>
                <td class="text-center align-top"></td>
                <td class="text-center align-top">1</td>
                <td class="text-center align-top">NIU</td>
                <td class="text-left align-top">
                    ANTICIPO: {{($p->document_type_id == '02')? 'FACTURA':'BOLETA'}} NRO. {{$p->number}}
                </td>
                <td class="text-right align-top">-{{ number_format($p->total, 2) }}</td>
                <td class="text-right align-top">0</td>
                <td class="text-right align-top">-{{ number_format($p->total, 2) }}</td>
            </tr>
            <tr>
                {{--<td colspan="9" class="border-box-tuexito"></td>--}} 
            </tr>
        @endforeach
     @endif

     @if($document->total_exportation > 0)
        <tr>
            <td colspan="6" class="text-right font-bold">OP. EXPORTACIÓN: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format($document->total_exportation, 2) }}</td>
        </tr>
     @endif
     @if($document->total_free > 0)
        <tr>
            <td colspan="6" class="text-right font-bold">OP. GRATUITAS: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format($document->total_free, 2) }}</td>
        </tr>
     @endif
     @if($document->total_unaffected > 0)
        <tr>
            <td colspan="6" class="text-right font-bold">OP. INAFECTAS: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format($document->total_unaffected, 2) }}</td>
        </tr>
     @endif
     @if($document->total_exonerated > 0)
        <tr>
            <td colspan="6" class="text-right font-bold">OP. EXONERADAS: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format($document->total_exonerated, 2) }}</td>
        </tr>
     @endif

     @if ($document->document_type_id === '07')
        @if($document->total_taxed >= 0)
            <tr>
                <td colspan="6" class="text-right">OP. GRAVADAS: {{ $document->currency_type->symbol }}</td>
                <td class="text-right">{{ number_format($document->total_taxed, 2) }}</td>
            </tr>
        @endif
     @elseif($document->total_taxed > 0)
        <tr>
            <td colspan="6" class="text-right">OP. GRAVADAS: {{ $document->currency_type->symbol }}</td>
            <td class="text-right">{{ number_format($document->total_taxed, 2) }}</td>
        </tr>
     @endif

     @if($document->total_plastic_bag_taxes > 0)
        <tr>
            <td colspan="6" class="text-right font-bold">ICBPER: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format($document->total_plastic_bag_taxes, 2) }}</td>
        </tr>
     @endif
     <tr>
        <td colspan="6" class="text-right">IGV: {{ $document->currency_type->symbol }}</td>
        <td class="text-right">{{ number_format($document->total_igv, 2) }}</td>
     </tr>

     @if($document->total_isc > 0)
        <tr>
            <td colspan="6" class="text-right font-bold">ISC: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format($document->total_isc, 2) }}</td>
        </tr>
     @endif

     @if($document->total_discount > 0 && $document->subtotal > 0)
        <tr>
            <td colspan="6" class="text-right font-bold">SUBTOTAL: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format($document->subtotal, 2) }}</td>
        </tr>
     @endif

     @if($document->total_discount > 0)
        <tr>
            <td colspan="6"
                class="text-right font-bold">{{(($document->total_prepayment > 0) ? 'ANTICIPO':'DESCUENTO TOTAL')}}
                : {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format($document->total_discount, 2) }}</td>
        </tr>
     @endif

     @if($document->total_charge > 0)
        @if($document->charges)
            @php
                $total_factor = 0;
                foreach($document->charges as $charge) {
                    $total_factor = ($total_factor + $charge->factor) * 100;
                }
            @endphp
            <tr>
                <td colspan="6" class="text-right font-bold">CARGOS ({{$total_factor}}
                    %): {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_charge, 2) }}</td>
            </tr>
        @else
            <tr>
                <td colspan="6" class="text-right font-bold">CARGOS: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_charge, 2) }}</td>
            </tr>
        @endif
     @endif

     @if($document->perception)
        <tr>
            <td colspan="6" class="text-right font-bold">IMPORTE TOTAL: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format($document->total, 2) }}</td>
        </tr>
        <tr>
            <td colspan="6" class="text-right font-bold">PERCEPCIÓN: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format($document->perception->amount, 2) }}</td>
        </tr>
        <tr>
            <td colspan="6" class="text-right font-bold">TOTAL A PAGAR: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format(($document->total + $document->perception->amount), 2) }}</td>
        </tr>
     @elseif($document->retention)
        <tr>
            <td colspan="6" class="text-right font-bold"
                style="font-size: 12px;">IMPORTE TOTAL: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold" style="font-size: 11px;">{{ number_format($document->total, 2) }}</td>
        </tr>
        {{--<tr>
            <td colspan="6" class="text-right">TOTAL RETENCIÓN ({{$document->retention->percentage * 100}}
                %): {{ $document->currency_type->symbol }}</td>
            <td class="text-right">{{ number_format($document->retention->amount, 2) }}</td>
        </tr>--}}
        {{--<tr>
            <td colspan="6" class="text-right">IMPORTE NETO: {{$document->currency_type->symbol }}</td>
            <td class="text-right">{{ number_format(($document->total - $document->retention->amount), 2)}}</td>
        </tr>--}}
     {{-- @else
        <tr>
            <td colspan="6" class="text-right">IMPORTE NETO: {{ $document->currency_type->symbol }}</td>
            <td class="text-right">{{ number_format(($document->total - $document->retention->amount), 2) }}</td>
        </tr> --}}
    @else
        <tr>
            <td colspan="6" class="text-right font-bold">TOTAL A PAGAR: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format($document->total, 2) }}</td>
        </tr>
    @endif

                  {{-- @if(($document->retention || $document->detraction) && $document->total_pending_payment > 0)
        <tr>
            <td colspan="6" class="text-right font-bold">M. PENDIENTE: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format($document->total_pending_payment, 2) }}</td>
        </tr>
              @endif--}}

     @if($balance < 0)
        <tr>
            <td colspan="6" class="text-right font-bold">VUELTO: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format(abs($balance),2, ".", "") }}</td>
        </tr>
      @endif


    </tbody>
</table>
<br>
<table class="full-width">
    <tr>
        <td width="70%" style=" text-align: top; vertical-align: top; font-size:14px;">
            @foreach(array_reverse( (array) $document->legends) as $row)
                @if ($row->code == "1000")
                    <p style="text-transform: uppercase;">Son: <span
                            class="font-bold">{{ $row->value }} {{ $document->currency_type->description }}</span></p>
                    <br><br/>
                    @if (count((array) $document->legends)>1)
                        <p><span class="font-bold">LEYENDAS:</span></p>
                    @endif
                @else
                <br/>
                {{--
                <div class="text-uppercase font-bold" width="45%">
                    <p> {{$row->code}}: {{ $row->value }} </p>
                </div>
                --}} 
                @endif
            @endforeach
        </td>
    </tr>
    <tr>
        <td>  
            <table class="" width="350px">
                {{-- CAMPO DETRACCION --}}
                @if ($document->detraction) 
                <tr class="border-box-tuexito">
                    <td class="p-2">
                        {{--<p>
                            <span class="font-bold">
                            Operación sujeta al Sistema de Pago de Obligaciones Tributarias
                            </span>
                        </p>--}}
                        <table class="full-width">
                            <tr>
                                <td class="text-uppercase font-bold" style="font-size:11px">OPERACIÓN SUJETA A DETRACCIÓN</td>
                            </tr>
                        </table>
                        <table class="full-width">
                            <tr>
                                <td width="130px" style="font-size:11px">N. CTA DETRACCIONES</td>
                                <td width="8px" style="font-size:11px">:</td>
                                <td style="font-size:11px">{{ $document->detraction->bank_account}}</td>
                            </tr>
                        </table>
                        <table class="full-width">
                            <tr>
                                <td width="123px" style="font-size:11px">BIEN O SERVICIO</td>
                                <td width="8px" style="font-size:11px">:</td>
                                @inject('detractionType', 'App\Services\DetractionTypeService')
                                <td width="220px" style="font-size:11px">{{$document->detraction->detraction_type_id}}
                                    - {{ $detractionType->getDetractionTypeDescription($document->detraction->detraction_type_id ) }}</td>
                            </tr>
                        </table>
                        <table class="full-width">
                            <tr>
                                <td width="130px" style="font-size:11px">P. DETRACCIÓN</td>
                                <td width="8px" style="font-size:11px">:</td>
                                <td style="font-size:11px">{{ $document->detraction->percentage}}%</td>
                            </tr>
                        </table>
                        <table class="full-width">
                            <tr>
                                <td width="130px" style="font-size:11px">MONTO DETRACCIÓN</td>
                                <td width="8px" style="font-size:11px">:</td>
                                <td style="font-size:11px">S/ {{ $document->detraction->amount}}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                @endif
                <tr class="m-3">
                    <td></td>
                </tr>

                
                {{-- CAMPO CONDICION DE PAGO --}}    
                <tr class="border-box-tuexito mt-2">
                    <td class="p-2 ">
                       @php
                        $paymentCondition = \App\CoreFacturalo\Helpers\Template\TemplateHelper::getDocumentPaymentCondition($document);
                        @endphp
                        <table class="full-width">
                           <tr>
                            <td style="font-size:11px">
                                <strong>CONDICIÓN DE PAGO: {{ $paymentCondition }} </strong>
                            </td>
                            </tr>
                        </table>
                         @if($document->payment_method_type_id)
                            <table class="full-width">
                                <tr>
                                <td style="font-size:11px">
                                    <strong>MÉTODO DE PAGO: </strong>{{ $document->payment_method_type->description }}
                                </td>
                              </tr>
                           </table>
                         @endif

                        @if ($document->payment_condition_id === '01')
                        @if($payments->count())
                            <table class="full-width">
                                <tr>
                                    <td style="font-size:11px"><strong>PAGOS:</strong></td>
                                </tr>
                                @php $payment = 0; @endphp
                                @foreach($payments as $row)
                                    <tr>
                                        <td>&#8226; {{ $row->payment_method_type->description }}
                                            {{--- {{ $row->reference ? $row->reference.' - ':'' }} {{ $document->currency_type->symbol }} {{ $row->payment + $row->change }}</td>--}} 
                                    </tr>

                                    

                                    @endforeach
                                    </tr>
                                 
                            

                              </table>
                              @endif
                               @else
                               <table class="full-width">
                                 @foreach($document->fee as $key => $quote)
                                <tr>
                                    <td style="font-size:11px">
                                        &#8226; {{ (empty($quote->getStringPaymentMethodType()) ? 'Cuota #'.( $key + 1) : $quote->getStringPaymentMethodType()) }}
                                        / Fecha: {{ $quote->date->format('d-m-Y') }} /
                                        Monto: {{ $quote->currency_type->symbol }}{{ $quote->amount }}</td>
                                </tr>
                                @endforeach
                                </tr>
                        </table>
                        @endif
                    </td>
                </tr>
            </table>           
        </td>
        <td>
            @if ($customer->department_id == 16)
                <br/><br/><br/>
                <div>
                    <center>
                        Representación impresa del Comprobante de Pago Electrónico.
                        <br/>Esta puede ser consultada en:
                        <br/><b>{!! url('/buscar') !!}</b>
                        <br/> "Bienes transferidos en la Amazonía
                        <br/>para ser consumidos en la misma".
                    </center>
                </div>
                <br/>
            @endif
            
            <br>

        
        </td>
        <td width="35%" class="text-right">
            <img src="data:image/png;base64, {{ $document->qr }}" style="margin-right: -10px;"/>
            <p style="font-size: 9px">Código Hash: {{ $document->hash }}</p>
        </td>
    </tr>
</table>
<br>
  {{-- NUMERO DE CUENTA --}}
  @if (isset($configurationInPdf) && $configurationInPdf->show_bank_accounts_in_pdf)
   <strong style="font-size:12px">CUENTAS BANCARIAS:</strong>
  <table class="full-width" >
        @if(in_array($document->document_type->id,['01','03']))
         @foreach($accounts as $account)
            <tr>
                {{-- Mostrar la imagen correspondiente al banco --}}
                <td style="width: 25px;">
                    @php
                        $logoPath = '';
                        switch ($account->bank->description) {
                            case "BANCO DE CREDITO DEL PERU":
                                $logoPath = $bcp_logo . '.png';
                                break;
                            case "CUENTA DE DETRACCIONES":
                                $logoPath = $bnacion_logo . '.png';
                            break;
                            case "BBVA CONTINENTAL":
                                $logoPath = $bbva_logo . '.png';
                                break;
                            case 'BANCO SCOTIABANK':
                                $logoPath = $scotiabank_logo . '.png';
                                break;
                            case 'INTERBANK':
                                $logoPath = $interbank_logo . '.png';
                                break;
                            default:
                                $logoPath = $empty_logo . '.png';
                                break;
                        }
                    @endphp
                    <img src="data:{{mime_content_type($logoPath)}};base64, {{base64_encode(file_get_contents($logoPath))}}" alt="{{$account->bank->description}}" style="width: 25px; height: 10px; padding: 1px 0;">
                </td>

                {{-- Descripción del banco --}}
                <td style="font-size:11px">
                    <strong>{{$account->bank->description}}</strong>
                    {{$account->currency_type->description}} Nº: {{$account->number}}
                    @if($account->cci)
                        <br>CCI: {{$account->cci}}
                    @endif
                </td>
            </tr>
         @endforeach
        @endif
    </table>
    @endif

   @if($document->retention)
    <br>
    <table class="full-width">
        <tr>
            <td>
                <strong>Información de la retención:</strong>
            </td>
        </tr>
        <tr>
            <td>Base imponible de la retención: S/ {{ $document->getRetentionTaxBase() }}
                {{-- S/ {{ round($document->retention->amount_pen / $document->retention->percentage, 2) }} --}}
            </td>
        </tr>
        <tr>
            <td>Porcentaje de la retención: {{ $document->retention->percentage * 100 }}%</td>
        </tr>
        <tr>
            <td>Monto de la retención: S/ {{ $document->retention->amount_pen }}</td>
        </tr>
    </table>
  @endif

   <br>
  <table class="full-width">
    @foreach($document->additional_information as $information)
    @if ($information)
        @if ($loop->first)
       <tr> <td class="fs-11" width="75px"><strong>Observaciones</strong></td></tr>
       <tr><td class="fs-11">{{ $information}}</td></tr>

        @endif
            @if(\App\CoreFacturalo\Helpers\Template\TemplateHelper::canShowNewLineOnObservation())
            {!! \App\CoreFacturalo\Helpers\Template\TemplateHelper::SetHtmlTag($information) !!}
            @endif
    @endif
   @endforeach

   @if (isset($configurationInPdf) && $configurationInPdf->show_seller_in_pdf)
    <tr>
        <td>
            <strong>Vendedor:</strong>
        </td>
    </tr>
    <tr>
        @if ($document->seller)
            <td>{{ $document->seller->name }}</td>
        @else
            <td>{{ $document->user->name }}</td>
        @endif
    </tr>
   @endif
   </table>
   @if ($document->terms_condition)
    <br>
    <table class="full-width">
        <tr>
            <td>
                <h6 style="font-size: 12px; font-weight: bold;">Términos y condiciones del servicio</h6>
                {!! $document->terms_condition !!}
            </td>
        </tr>
    </table>
   @endif
</body>
</html>
