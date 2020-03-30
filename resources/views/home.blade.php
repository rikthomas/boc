@extends('layouts.app')

@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div>
                <h1>UCLH VIE Regulator Flowrate</h1>
            </div>
            <br><br>
            <div id="container" style="width:100%; height:400px;"></div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
Highcharts.chart('container', {
    chart: {
        type: 'column'
    },
    title: {
        text: 'Daily Average Flowrate'
    },
    xAxis: {
        categories: dates,
        crosshair: true,
    },
    yAxis: {
        min: 0,
        title: {
            text: 'Flow L/min'
        }
    },
    tooltip: {
        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
        pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
            '<td style="padding:0"><b>{point.y:.1f}L/min</b></td></tr>',
        footerFormat: '</table>',
        shared: true,
        useHTML: true
    },
    plotOptions: {
        column: {
            pointPadding: 0.2,
            borderWidth: 0
        },
        series: {
            dataLabels: {
                enabled: false
            }
        }
    },
    credits: {
        enabled: false,
    },
    series: [{
        data: avg_r_result,
        name: 'Average Flow',
        showInLegend: false
    }],
});

</script>

@endpush