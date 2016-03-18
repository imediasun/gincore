var previousPoint = null, previousLabel = null;
$.fn.flotUseTooltip = function () {
    $(this).bind("plothover", function (event, pos, item) {
        if (item) {
            if ((previousLabel != item.series.label) || (previousPoint != item.dataIndex)) {
                previousPoint = item.dataIndex;
                previousLabel = item.series.label;
                $("#tooltip").remove();

                var x = item.datapoint[0];
                var y = item.datapoint[1];
                var date = new Date(x);
                var color = item.series.color;
                var m = date.getMonth() + 1;
                showTooltip(item.pageX, item.pageY, color,
                        date.getDate() + "." + (m < 10 ? "0"+m : m) +
                        ": <strong>" + y + "</strong>");
            }
        } else {
            $("#tooltip").remove();
            previousPoint = null;
        }

    });
};

function showTooltip(x, y, color, contents) {
    $('<div id="tooltip">' + contents + '</div>').css({
        position: 'absolute',
        display: 'none',
        width: 90,
        textAlign: 'center',
        top: y - 30,
        left: x - 47,
        border: '2px solid ' + color,
        borderRadius: '5px',
        backgroundColor: '#fff',
        opacity: 0.9
    }).appendTo("body").fadeIn(200);
}

function gd(year, month, day) {
    return Date.UTC(year, month - 1, day);
}

function init_conv_chart(id,data1,data2,data3,init_visitors,legend_labels, tick_size){
//    init_visitors = true;
    var chartIncomeData = [];
    chartIncomeData.push({
            label: "&nbsp;"+legend_labels[0],
            data: data2,
            yaxis: 2
        },
        {
            label: "&nbsp;"+legend_labels[1],
            data: data3,
            yaxis: 2
        }
    );
    if(init_visitors){
        chartIncomeData.push({
            label: "&nbsp;"+legend_labels[2],
            data: data1,
            yaxis: 1
        });
    }
    var chartIncomeOptions = {
        series: {
            lines: {
                show: true,
                lineWidth: 1,
//                fill: true,
//                fillColor: "#64cc34"
            },
            points: {
                show: true,
                radius: 2,
                symbol: "circle",
                lineWidth: 0,
                fill: true,
                fillColor: "#000000"
            },
            shadowSize: 0
        },
        colors: ['#FFC90E','#22B14C',"#ED1C24"],
        grid: {
            backgroundColor: "#ffffff",
            tickColor: "#f0f0f0",
            borderWidth: 1,
            borderColor: "#f0f0f0",
            color: "#6a6c6f",
            hoverable: true
        },
        xaxis: {
            tickSize: [tick_size||1, "day"],
            mode: "time",
            timeformat: "%d.%m",
            tickDecimals: 0,
        },
        yaxes: [
            {
                show: init_visitors,
                min: 0,
                position: "right",
                tickDecimals: 0,
                mode: "number",
                font:{
                    color: '#ED1C24'
                }
            },
            {
                min: 0,
                position: "left",
                tickDecimals: 0,
                mode: "number",
                font:{
                    color: 'yellowgreen'
                }
            }

        ],
        legend: {
            position: "ne"
        }
    };
    var plot = $.plot($(id), chartIncomeData, chartIncomeOptions);
    $(id).flotUseTooltip();
    $(window).resize(function () {
        plot.resize();
    });
}

function init_cash_chart(data){
    var chartIncomeData = [
        {
            label: "line",
            data: data
        }
    ];

    var chartIncomeOptions = {
        series: {
            lines: {
                show: true,
                lineWidth: 0,
                fill: true,
                fillColor: "#64cc34"

            }
        },
        colors: ["#62cb31"],
        grid: {
            show: false
        },
        legend: {
            show: false
        }
    };

    $.plot($("#flot-cash-chart"), chartIncomeData, chartIncomeOptions);

}

function getGet(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
            results = regex.exec(location.search);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}

function setGet(params) {
    var str = window.location.search.replace("?", "");
    var obj = {};
    var pairs = str.split('&');
    for (i in pairs) {
        var split = pairs[i].split('=');
        obj[decodeURIComponent(split[0])] = decodeURIComponent(split[1]);
    }
    var result = '?' + $.param(params);
    window.location = result;
}

$(function(){
    var date_start = getGet('ds');
    var date_end = getGet('de');

    $('#daterange').daterangepicker(
        {
            ranges: {
                'Today': [new Date(), new Date()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), new Date()],
                'Last 30 Days': [moment().subtract(29, 'days'), new Date()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            opens: 'right',
            startDate: date_start ? date_start : moment().format("YYYY-MM-01"),
            endDate: date_end ? date_end : moment().format("YYYY-MM-DD"),
            locale: {
                firstDay: 1,
                format: 'YYYY-MM-DD',
            }
        },
        function (start, end) {
            date_start = start.format("YYYY-MM-DD");
            date_end = end.format("YYYY-MM-DD");
            setGet({ds: start.format("YYYY-MM-DD"), de: end.format("YYYY-MM-DD")});
            $('#daterange span').html(start.format('D/M/YY') + ' - ' + end.format('D/M/YY'));
        }
    );
});

function init_chart(id, data, tickSize) {
    var chartIncomeData = [];
    $.each(data, function (index, element) {
        chartIncomeData.push({
            label: "&nbsp;" + element.legend,
            data: element.points,
            yaxis: 2
        });
    });
    var chartIncomeOptions = {
        series: {
            lines: {
                show: true,
                lineWidth: 1,
            },
            points: {
                show: true,
                radius: 2,
                symbol: "circle",
                lineWidth: 0,
                fill: true,
                fillColor: "#000000"
            },
            shadowSize: 0
        },
        colors: ['#FFC90E', '#22B14C', "#ED1C24"],
        grid: {
            backgroundColor: "#ffffff",
            tickColor: "#f0f0f0",
            borderWidth: 1,
            borderColor: "#f0f0f0",
            color: "#6a6c6f",
            hoverable: true
        },
        xaxis: {
            tickSize: [tickSize || 1, "day"],
            mode: "time",
            timeformat: "%d.%m",
            tickDecimals: 0,
        },
        yaxes: [
            {
                min: 0,
                position: "left",
                tickDecimals: 0,
                mode: "number",
                font: {
                    color: 'yellowgreen'
                }
            }
        ],
        legend: {
            position: "ne"
        }
    };
    var plot = $.plot($(id), chartIncomeData, chartIncomeOptions);
    $(id).flotUseTooltip();
    $(window).resize(function () {
        plot.resize();
    });
}

function expand(_this) {
    $(_this).parent().removeClass('h-eq-250').find('.collapse-button').first().show();
    $(_this).hide();
    return false;
}

function collapse(_this) {
    $(_this).parent().addClass('h-eq-250').find('.expand-button').first().show();
    $(_this).hide();
    return false;
}

function set_step(type) {
    var href = window.location.href, parts = href.split('#'), re = new RegExp('/\?/');

    if (re.test(parts[0])) {
        parts[0] = parts[0].replace(/&month=/g, '').replace(/&day=/g, '').replace(/&week=/g, '');
        parts[0] = parts[0] + '&' + type + '=';

    } else {
        parts[0] = parts[0] + '?' + type + '=';
    }
    window.location = parts[0];
    return false;
}
