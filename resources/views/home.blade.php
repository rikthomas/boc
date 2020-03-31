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
            <br><br>
            <div id="container_time" style="width:100%; height:400px;"></div>
            <br><br>
            <form action="/upload" method="POST" enctype="multipart/form-data" class="dropzone" id="dzupload">
                <div class="dz-message" data-dz-message><span>Drop BOC export here or click for manual upload</span></div>
                {{ csrf_field() }}
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')

<script>
 Dropzone.options.dzupload = {
    maxFilesize: 1,
    acceptedFiles: ".xls",
    success: function() {
        location.reload();
    },
};
</script>

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
            '<td style="padding:0"><b>{point.y:.1f} L/min</b></td></tr>',
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


    $.getJSON(
    '/data',
    function (data) {

        Highcharts.chart('container_time', {
            chart: {
                zoomType: 'x'
            },
            title: {
                text: 'Regulator Flowrate'
            },
            subtitle: {
                text: document.ontouchstart === undefined ?
                    'Click and drag in the plot area to zoom in' : 'Pinch the chart to zoom in'
            },
            xAxis: {
                type: 'datetime'
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'Flow L/min'
                }
            },
            legend: {
                enabled: false
            },
            plotOptions: {
                area: {
                    fillColor: {
                        linearGradient: {
                            x1: 0,
                            y1: 0,
                            x2: 0,
                            y2: 1
                        },
                        stops: [
                            [0, Highcharts.getOptions().colors[0]],
                            [1, Highcharts.color(Highcharts.getOptions().colors[0]).setOpacity(0).get('rgba')]
                        ]
                    },
                    marker: {
                        radius: 2
                    },
                    lineWidth: 1,
                    states: {
                        hover: {
                            lineWidth: 1
                        }
                    },
                    threshold: null
                }
            },
            credits: {
                enabled: false,
            },
            series: [{
                type: 'area',
                name: 'Flow L/min',
                data: data
            }]
        });
    }
);
</script>

@endpush