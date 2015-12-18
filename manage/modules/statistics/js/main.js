$(document).ready(function () {

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
		format: 'YYYY-MM-DD',
                firstDay: 1
            }
        },
        function (start, end) {
            date_start = start.format("YYYY-MM-DD");
            date_end = end.format("YYYY-MM-DD");
            setGet({ds: start.format("YYYY-MM-DD"), de: end.format("YYYY-MM-DD")});
            $('#daterange span').html(start.format('D/M/YY') + ' - ' + end.format('D/M/YY'));
            getStatistics(start.format("YYYY-MM-DD"), end.format("YYYY-MM-DD"));
            getTable(start.format("YYYY-MM-DD"), end.format("YYYY-MM-DD"));
        }
    );

    var updates = 0;
    var loading = $('#report tbody .loading').hide().clone();
    var nodata = $('#report tbody .nodata').hide().clone();

    getStatistics(date_start, date_end);
    getTable(date_start, date_end);

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
        var result = '?' + $.param($.extend({}, obj, params));

        window.history.pushState(
            {previous_url: document.location.href},
            document.title,
            result
        );
    }

    function getStatistics(start, end) {
        $('#date-range-statistics').html('<i class="fa fa-spinner fa fa-spin"></i>');

        var jsondata = '';
        if (typeof start != 'undefined' && start != null) {
            jsondata += '&start=' + encodeURIComponent(start);
        }
        if (typeof end != 'undefined' && end != null) {
            jsondata += '&end=' + encodeURIComponent(end);
        }

        $.ajax({
            url: prefix + module + '/ajax/?act=get-statistics',
            data: jsondata,
            type: 'post',
            success: function (data) {
                $('#date-range-statistics').html(data);
            }
        });
    }

    $(this).on('click', '.sub-collapsible > a:not(.disabled)', function() {
        var $self = $(this);
        var $tr = $self.closest('tr');
        var $trc = $tr.next();
        var method = 'hide';

        if ($tr.hasClass('sub-child-loaded')) {
            if ($self.hasClass('collapsed')) {
                $self.removeClass('collapsed').addClass('expanded');
                method = 'show';
            } else if ($self.hasClass('expanded')) {
                $self.removeClass('expanded').addClass('collapsed');
            }
            while($trc.hasClass('sub-expand-child')) {
                $("td", $trc)[method]();
                $trc = $trc.next();
            }
            return;
        }

        $self.addClass('disabled');
        var date_start = $tr.data('date_start');
        var date_end = $tr.data('date_end');
        var group_referers = $tr.data('group_referers');
        var data = {start: date_start, end: date_end, group_id: group_referers};
        if($tr.parents('tfoot').length){
            data.start = getGet('ds');
            data.end = getGet('de');
        }
        $.ajax({
            url: prefix + module + '/ajax/?act=get-kpi-groups',
            data: data,
            type: 'post',
            success: function (data) {
                var html = '';
                if (typeof data == 'object' && data.length == 0) {
                    //html += nodata.html();
                } else {
                    for (var row in data) {
                        if (typeof data[row]['referer_name'] != 'undefined') {
                            html += '<tr class="expand-child sub-expand-child">';
                            html += '<td></td>';
                            html += '<td class="text-right" data-sortvalue="'+$tr.find('td[data-sortvalue]').data('sortvalue')+'">' + data[row]['referer_name'] + '</td>';
                            html += kpi_row(data[row]);
                            html += '</tr>';
                        }
                    }
                }
                $self.removeClass('collapsed').addClass('expanded');
                $tr.addClass('sub-child-loaded').after(html);
//                console.log(html);
                $self.removeClass('disabled');
                $("#report").trigger("update");
            }
        });

        return false;
    });
    /*function getGraph(start, end, goal) {

     $('#graph .placeholder').html('<div style="padding: 40px 0 0 0; text-align: center; font-size: 18px"><i class="fa fa-spinner fa fa-spin"></i></div>');
     $('#graph .legend span').html('<i class="fa fa-spinner fa fa-spin"></i>');

     var jsonurl = '/lpg/graph.json?filters[ad]=1&filters[goal]=' + encodeURIComponent(goal);
     if (typeof location.search == 'string' && location.search.length > 0)
     jsonurl += '&' + location.search.substring(1);

     if (typeof start != 'undefined' && typeof end != 'undefined' && start != null & end != null)
     jsonurl += '&start=' + encodeURIComponent(start) + '&end=' + encodeURIComponent(end);

     $.getJSON(jsonurl, function (data) {
     if (data.length == 0) {
     $('#report tbody').html(nodata.html());
     } else {

     var people = [], conversions = [];
     for (var i in data.graph) {
     var row = data.graph[i];
     people.push([moment(row.date).valueOf(), parseInt(row.people)]);
     conversions.push([moment(row.date).valueOf(), parseInt(row.conversions)])
     }

     HighchartsGraph('graph', people, conversions, data.people, data.conversions);

     }
     });
     }*/

    function getTable(start, end) {

        $('#report tbody').html(loading.html());
        $('#report tfoot').html('');

        var jsondata = '';
        if (typeof start != 'undefined' && start != null) {
            jsondata += '&start=' + encodeURIComponent(start);
        }
        if (typeof end != 'undefined' && end != null) {
            jsondata += '&end=' + encodeURIComponent(end);
        }

        $.ajax({
            url: prefix + module + '/ajax/?act=get-kpi',
            data: jsondata,
            type: 'post',
            success: function (data) {

                var html = '';
                var clicks = 0, visits = 0, views = 0, new_clients = 0, users = 0, paid = 0, rate = 0,
                    bounces = 0, qty_calls = 0, qty_requests = 0, qty_orders_by_requests = 0,
                    qty_orders_wo_requests = 0, qty_all_orders = 0, qty_orders_payments = 0,
                    created_orders_summ = 0, orders_summ = 0, avg_check = 0,
                    referer_expense = 0, expense = 0; /*income = 0, roi = 0, paid1 = 0*/;

                if (typeof data == 'object' && data.length == 0) {
                    $('#report tbody').html(nodata.html());
                } else {
                    for (var row in data) {

                        var year = data[row]['yearweek'].split('-');
                        data[row]['roi'] = data[row]['income'] && data[row]['expense'] > 0 ? data[row]['income'] / data[row]['expense'] * 100 : 0;
                        data[row]['cpo'] = data[row]['referer_expense'] && data[row]['paid'] > 0 ? data[row]['referer_expense'] / data[row]['paid'] : 0;
//                        data[row]['cpo1'] = data[row]['referer_expense'] && data[row]['paid1'] > 0 ? data[row]['referer_expense'] / data[row]['paid1'] : 0;
//                        data[row]['ctr'] = data[row]['clicks'] && data[row]['visits'] > 0 ? data[row]['clicks'] / data[row]['visits'] * 100 : 0;

                        var date_start = DayOfWeek(year[0], year[1], 1, 'YYYY-MM-DD');
                        var date_end = DayOfWeek(year[0], year[1], 7, 'YYYY-MM-DD');
                        html += '<tr data-date_start="' + date_start + '" data-date_end="' + date_end + '">';
                        html += '<td class="collapsible"></td>';
                        var date_start = DayOfWeek(year[0], year[1], 1, 'DD.MM.YYYY');
                        var date_end = DayOfWeek(year[0], year[1], 7, 'DD.MM.YYYY');
                        html += '<td class="collapsible_alt" data-sortValue="' + date_start + '">' + date_start + '-' + date_end + ' </td>';
                        html += kpi_row(data[row]);
                        html += '</tr>';

                        clicks += data[row]['clicks'] ? parseInt(data[row]['clicks']) : 0;
                        visits += data[row]['visits'] ? parseInt(data[row]['visits']) : 0;
                        views += data[row]['views'] ? parseInt(data[row]['views']) : 0;
                        new_clients += data[row]['new_clients'] ? parseInt(data[row]['new_clients']) : 0;
                        rate += data[row]['rate'] ? parseInt(data[row]['rate']) : 0;
                        bounces += data[row]['bounces'] ? parseInt(data[row]['bounces']) : 0;
                        users += data[row]['users'] ? parseInt(data[row]['users']) : 0;
                        qty_calls += data[row]['qty_calls'] ? parseInt(data[row]['qty_calls']) : 0;
                        qty_requests += data[row]['qty_requests'] ? parseInt(data[row]['qty_requests']) : 0;
                        qty_orders_by_requests += data[row]['qty_orders_by_requests'] ? parseInt(data[row]['qty_orders_by_requests']) : 0;
                        qty_all_orders += data[row]['qty_all_orders'] ? parseInt(data[row]['qty_all_orders']) : 0;
                        qty_orders_wo_requests += data[row]['qty_orders_wo_requests'] ? parseInt(data[row]['qty_orders_wo_requests']) : 0;
                        qty_orders_payments += data[row]['qty_orders_payments'] ? parseInt(data[row]['qty_orders_payments']) : 0;
                        created_orders_summ += data[row]['created_orders_summ'] ? parseInt(data[row]['created_orders_summ']) : 0;
                        orders_summ += data[row]['orders_summ'] ? parseInt(data[row]['orders_summ']) : 0;
                        avg_check += data[row]['avg_check'] ? parseInt(data[row]['avg_check']) : 0;
                        expense += data[row]['expense'] ? parseFloat(data[row]['expense']) : 0;
//                        income += data[row]['income'] ? parseFloat(data[row]['income']) : 0;
                        referer_expense += data[row]['referer_expense'] ? parseFloat(data[row]['referer_expense']) : 0;
//                        paid += data[row]['paid'] ? parseFloat(data[row]['paid']) : 0;
//                        paid1 += data[row]['paid1'] ? parseFloat(data[row]['paid1']) : 0;
                    }

                    $('#report tbody').html(html);

                    if (updates == 0) {
                        $("#report").tablesorter({
                            dateFormat : "ddmmyyyy",
                            textExtraction: {
                                1 : function (node) {
                                    return $(node).data('sortvalue');
                                }
                            },
                            sortForce  : [[1,0]],
                            sortList: [
                                [1, 0]
                            ],
                            widthFixed : true,
                            widgets: ['zebra', 'stickyHeaders'],
                            widgetOptions: {
                                stickyHeaders_offset : 40
                            },
//                            onRenderHeader: function () {
//                                this.wrapInner("<span></span>");
//                            },
                            debug: false
                        });
                    } else {
                        $('#report').trigger("update");
                        $('#report').trigger("sorton", [
                            [
                                [1, 0]
                            ]
                        ]);
                    }
                    
                    html = '<tr class="expand-child" data-date_start="'+getGet('ds')+'" data-date_end="'+getGet('de')+'">';
                    html += '<td class="collapsible"></td><td>' + L['total'] +  ':</td>';
                    html += '<td>' + visits + '</td>';
                    html += '<td>' + clicks + '</td>';
                    html += '<td>' + (visits > 0 ? clicks / visits * 100 : 0).toFixed(2) + '</td>';
                    html += '<td>' + views + '</td>';
                    html += '<td>' + new_clients + '</td>';
                    html += '<td>' + (users > 0 ? new_clients / users * 100 : 0).toFixed(2) + '</td>';
                    html += '<td>' + (bounces > 0 ? rate / bounces : 0).toFixed(2) + '</td>';
                    html += '<td>' + qty_calls + '</td>';
                    html += '<td>' + qty_requests + '</td>';
                    html += '<td>' + qty_orders_by_requests + '</td>';
                    html += '<td>' + qty_orders_wo_requests + '</td>';
                    html += '<td>' + qty_all_orders + '</td>';
                    html += '<td>' + qty_orders_payments + '</td>';
                    html += '<td>' + created_orders_summ + '</td>';
                    html += '<td>' + orders_summ + '</td>';
                    html += '<td>' + avg_check + '</td>';
                    html += '<td>' + (qty_orders_payments > 0 ? referer_expense / qty_orders_payments : 0).toFixed(2) + '</td>';
                    html += '<td>' + (expense > 0 ? orders_summ / expense * 100 : 0).toFixed(2) + '</td>';
                    html += '</tr>';
                    $('#report tfoot').html(html);
                    
                    /*
                     * td.collapsible = collapse to the first table row and show +/-
                     * td.collapsible_alt = anchor to order number
                     */
                    $('#report').collapsible("td.collapsible", {
                        collapse: true,
                        onLoad: function (do_collapsible, $tr, $a) {

                            if ($tr.hasClass('child-loaded')) {
                                do_collapsible();
                                return;
                            }

                            if ($a.hasClass('disabled')) {
                                return;
                            }

                            $a.addClass('disabled');
                            var date_start = $tr.data('date_start');
                            var date_end = $tr.data('date_end');

                            $.ajax({
                                url: prefix + module + '/ajax/?act=get-kpi-groups',
                                data: {start: date_start, end: date_end},
                                type: 'post',
                                success: function (data) {
                                    var html = '';
                                    if (typeof data == 'object' && data.length == 0) {
                                        //html += nodata.html();
                                    } else {
                                        for (var row in data) {
                                            if (typeof data[row]['yearweek'] != 'undefined') {
                                                var year = data[row]['yearweek'].split('-');
                                                var date_start = DayOfWeek(year[0], year[1], 1, 'YYYY-MM-DD');
                                                var date_end = DayOfWeek(year[0], year[1], 7, 'YYYY-MM-DD');
                                                var group_referers = data[row]['group_referers'];
                                                html += '<tr class="expand-child" data-date_start="' + date_start + '" data-date_end="' + date_end + '" data-group_referers="' + group_referers + '">';
                                                var date_start = DayOfWeek(year[0], year[1], 1, 'DD.MM.YYYY');
                                                html += '<td></td><td data-sortvalue="'+date_start+'"><div class="sub-collapsible"><a class="collapsed" href="#"></a></div>'
                                                html += '' + (data[row]['group'] ? data[row]['group'] : '') + '</td>';
                                                html += kpi_row(data[row]);
                                                html += '</tr>';
                                            }
                                        }
                                    }
                                    $tr.addClass('child-loaded').after(html);

                                    $("#report").trigger("update");
                                    $a.removeClass('disabled');
                                    do_collapsible();
                                }
                            });
                        }
                    });
                    updates++;
                }
            }
        });
    }

    function kpi_row(row) {
        row['roi'] = row['orders_summ'] && row['expense'] > 0 ? row['orders_summ'] / row['expense'] * 100 : 0;
        row['cpo'] = row['referer_expense'] && row['qty_orders_payments'] > 0 ? row['referer_expense'] / row['qty_orders_payments'] : 0;
//        row['cpo1'] = row['referer_expense'] && row['paid1'] > 0 ? row['referer_expense'] / row['paid1'] : 0;
        row['ctr'] = row['clicks'] && row['visits'] > 0 ? row['clicks'] / row['visits'] * 100 : 0;

        var html = '<td>' + (row['visits'] ? parseInt(row['visits']) : 0) + '</td>';
        html += '<td>' + (row['clicks'] ? parseInt(row['clicks']) : 0) + '</td>';
        html += '<td>' + (row['ctr'] ? parseFloat(row['ctr']) : 0).toFixed(2) + '</td>';
        html += '<td>' + (row['views'] ? parseInt(row['views']) : 0) + '</td>';
        html += '<td>' + (row['new_clients'] ? parseInt(row['new_clients']) : 0) + '</td>';
        html += '<td>' + (row['new_clients_percent'] ? parseFloat(row['new_clients_percent']) : 0).toFixed(2) + '</td>';
        html += '<td>' + (row['rate_percent'] ? parseFloat(row['rate_percent']) : 0).toFixed(2) + '</td>';
        html += '<td>' + (row['qty_calls'] ? parseInt(row['qty_calls']) : 0) + '</td>';
        html += '<td>' + (row['qty_requests'] ? parseInt(row['qty_requests']) : 0) + '</td>';
        html += '<td>' + (row['qty_orders_by_requests'] ? parseInt(row['qty_orders_by_requests']) : 0) + '</td>';
        html += '<td>' + (row['qty_orders_wo_requests'] ? parseInt(row['qty_orders_wo_requests']) : 0) + '</td>';
        html += '<td>' + (row['qty_all_orders'] ? parseInt(row['qty_all_orders']) : 0) + '</td>';
        html += '<td>' + (row['qty_orders_payments'] ? parseInt(row['qty_orders_payments']) : 0) + '</td>';
        html += '<td>' + (row['created_orders_summ'] ? parseInt(row['created_orders_summ']) : 0) + '</td>';
        html += '<td>' + (row['orders_summ'] ? parseInt(row['orders_summ']) : 0) + '</td>';
        html += '<td>' + (row['avg_check'] ? parseInt(row['avg_check']) : 0) + '</td>';
        html += '<td>' + (row['cpo'] ? parseFloat(row['cpo']) : 0).toFixed(2) + '</td>';
        html += '<td>' + (row['roi'] ? parseFloat(row['roi']) : 0).toFixed(2) + '</td>';
//        html += '<td>' + (row['paid'] ? parseInt(row['paid']) : 0) + '</td>';
//        html += '<td>' + (row['paid1'] ? parseInt(row['paid1']) : 0) + '</td>';
//        html += '<td>' + (row['expense'] ? parseFloat(row['expense']) : 0).toFixed(2) + '</td>';
//        html += '<td>' + (row['income'] ? parseFloat(row['income']) : 0).toFixed(2) + '</td>';
//        html += '<td>' + (row['avg_bill'] ? parseFloat(row['avg_bill']) : 0).toFixed(2) + '</td>';
//        html += '<td>' + (row['cpo'] ? parseFloat(row['cpo']) : 0).toFixed(2) + '</td>';
//        html += '<td>' + (row['cpo1'] ? parseFloat(row['cpo1']) : 0).toFixed(2) + '</td>';
//        html += '<td>' + (row['roi'] ? parseFloat(row['roi']) : 0).toFixed(2) + '</td>';

        return html;
    }

    function DayOfWeek(year, week, num, format) {
        var d = new Date(year, 0, (week - 1) * 7);
        var day = d.getDay();
        var diff = d.getDate() - day + num;
        var date = new Date(d.setDate(diff));

        return moment(date).format(format);
    }

    /*function HighchartsGraph(target, people, conversions, total_people, total_conversions, hide_legend) {

     var chartoptions = {
     global: {
     useUTC: false
     },
     chart: {
     renderTo: 'graph',
     margin: [0, 10, 25, 10],
     borderRadius: 0,
     backgroundColor: '#ffffff'
     },
     title: {
     text: null
     },
     colors: ['#0088cc', '#339900'],
     credits: {
     enabled: false
     },
     legend: {
     enabled: false
     },
     plotOptions: {
     area: {
     lineWidth: 2.5,
     fillOpacity: .1,
     marker: {
     lineColor: '#fff',
     lineWidth: 1,
     radius: 3.5,
     symbol: 'circle'
     },
     shadow: false
     },
     column: {
     lineWidth: 16,
     shadow: false,
     borderWidth: 0,
     groupPadding: .05
     }
     },
     xAxis: {
     type: 'datetime',
     title: {
     text: null
     },
     tickmarkPlacement: 'on',
     dateTimeLabelFormats: {
     day: '%b %e'
     },
     gridLineColor: '#eeeeee',
     gridLineWidth: .5,
     labels: {
     style: {
     color: '#999999'
     }
     }
     },
     yAxis: [
     {
     offset: -30,
     showFirstLabel: false,
     showLastLabel: false,
     title: {
     text: null
     },
     gridLineColor: '#eeeeee',
     gridLineWidth: .5,
     zIndex: 2,
     labels: {
     align: 'right',
     style: {
     color: '#999999'
     }
     }
     },
     {
     offset: -10,
     showFirstLabel: false,
     showLastLabel: false,
     title: {
     text: null
     },
     opposite: true,
     gridLineColor: '#eeeeee',
     gridLineWidth: .5,
     zIndex: 1,
     labels: {
     align: 'right',
     style: {
     color: '#999999'
     }
     }
     }
     ],
     tooltip: {
     shadow: false,
     borderRadius: 3,
     shared: true,
     formatter: function () {
     var line1 = '<span style="font-size: 10px">' + moment(this.x).format('dddd, MMM D, YYYY') + '</span>';
     var line2 = '<span style="color: #08c">People:</span>  <b>' + this.points[0].y + '</b>';
     var line3 = '<span style="color: #390">Conversions:</span>  <b>' + this.points[1].y + '</b>';
     return line1 + '<br />' + line2 + '<br />' + line3;
     }
     },
     series: [
     {
     name: '',
     data: [],
     pointStart: 0,
     pointInterval: 24 * 3600 * 1000,
     type: 'column'
     },
     {
     name: '',
     data: [],
     pointStart: 0,
     pointInterval: 24 * 3600 * 1000,
     type: 'area',
     yAxis: 1
     }
     ]
     };

     chartoptions.series[0].name = 'People';
     chartoptions.series[0].data = people;
     chartoptions.series[0].pointStart = people[0][0].valueOf();

     chartoptions.series[1].name = 'Conversions';
     chartoptions.series[1].data = conversions;
     chartoptions.series[1].pointStart = conversions[0][0].valueOf();

     if (typeof hide_legend == 'undefined') {
     var html = '<div class="row">';
     html += '<div class="placeholder col-md-10"></div>';
     html += '<div class="legend col-md-2">';
     html += '<div class="people"><span>' + add_commas(total_people) + '</span><h4>People</h4></div>';
     html += '<div class="conversions"><span>' + add_commas(total_conversions) + '</span><h4>Conversions</h4></div>';
     html += '</div>';
     html += '</div>';
     $('#' + target).html(html);
     chartoptions.chart.renderTo = $('#' + target + ' .placeholder')[0];
     } else {
     chartoptions.chart.renderTo = $('#' + target)[0];
     }

     var graph = new Highcharts.Chart(chartoptions);
     }

     function add_commas(val) {
     if (!isFinite(val) || val == 0 || val == null) return 0;
     val = val + '';
     var whole = val;
     var decimal = null;

     if (val.indexOf('.') != -1) {
     var parts = val.split('.');
     whole = parts[0];
     decimal = parts[1];
     }

     var result = '';
     var pos = 0;
     for (var i = whole.length - 1; i >= 0; i--) {
     if (pos > 0 && pos % 3 == 0)
     result = ',' + result;
     result = whole.charAt(i) + result;
     pos++;
     }

     if (decimal != null)
     result = result + '.' + decimal;

     return result;
     }*/

});
