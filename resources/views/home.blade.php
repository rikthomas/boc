@extends('layouts.app')

@section('content')

<div class="container">  
    <!-- Modal -->
    <div class="modal fade" id="myModal" role="dialog">
      <div class="modal-dialog modal-lg">
      
        <!-- Modal content-->
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">Instructions</h4>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
          </div>
          <div class="modal-body">
            <p>
                <ul>
                    <li>This dashboard updates daily at 0700 with readings from the previous 30 days</li>
                    <li>If you want to pull a different time period got to <a href = "https://dpc.boc.com/" target="_blank">dpc.boc.com</a></li>
                    <li>Username is <b>UCLHNHS</b></li>
                    <li>Password is <b>Oxygen@2</b></li>
                    <li>Login and click 'Download'</li>
                    <li>Select a reasonable date range (2 - 3 months)</li>
                    <li>Check boxes for <b>MAIN TANK, *tank A and *tank B</b> (LOX-TANK-2, LOX-TANK-4 & LOX-TANK-5)</li>
                    <li>Download file and drag into tool at the bottom of the page</li>
                </ul>
            </p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          </div>
        </div>
        
      </div>
    </div>
    
  </div>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div>
                <h1>UCLH VIE Flowrate Monitoring
                <button type="button" class="btn btn-info float-right" data-toggle="modal" data-target="#myModal">How to use the tool</button></h1>
            </div>
            <br>
            <div id="container" style="width:100%; height:400px;"></div>
            <br><br>
            <div id="container_time" style="width:100%; height:400px;"></div>
            <br><br>
            <div id="container_nhnn" style="width:100%; height:400px;"></div>
            <br><br>
            <div id="container_time_nhnn" style="width:100%; height:400px;"></div>
            <br><br>
            <form action="/vie/current/public/upload" method="POST" enctype="multipart/form-data" class="dropzone" id="dzupload">
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
        text: 'UCH Daily Average Flowrate (Max 5000L/min)'
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

Highcharts.chart('container_nhnn', {
    chart: {
        type: 'column'
    },
    title: {
        text: 'NHNN Daily Average Flowrate (Max 3000L/min)'
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
        data: avg_nhnn_result,
        name: 'Average Flow',
        showInLegend: false,
        color: '#40e0d0'
    }],
});


    $.getJSON(
    '/vie/current/public/data',
    //'/data',
    function (data) {

        Highcharts.chart('container_time', {
            chart: {
                zoomType: 'x'
            },
            title: {
                text: 'UCH Regulator Flowrate'
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

$.getJSON(
    '/vie/current/public/data_nhnn',
    //'/data_nhnn',
    function (data) {

        Highcharts.chart('container_time_nhnn', {
            chart: {
                zoomType: 'x'
            },
            title: {
                text: 'NHNN Regulator Flowrate'
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
                            [0, '#40e0d0'],
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
                data: data,
                color: '#40e0d0'
            }]
        });
    }
);
</script>

@endpush