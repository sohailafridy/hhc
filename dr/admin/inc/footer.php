
   <!-- Required Jqurey -->
   <script src="<?php echo BASE_URL; ?>admin/inc/assets/plugins/Jquery/dist/jquery.min.js"></script>
   <script src="<?php echo BASE_URL; ?>admin/inc/assets/plugins/jquery-ui/jquery-ui.min.js"></script>
   <script src="<?php echo BASE_URL; ?>admin/inc/assets/plugins/tether/dist/js/tether.min.js"></script>

   <!-- Required Fremwork -->
   <script src="<?php echo BASE_URL; ?>admin/inc/assets/plugins/bootstrap/js/bootstrap.min.js"></script>

   <!-- Scrollbar JS-->
   <script src="<?php echo BASE_URL; ?>admin/inc/assets/plugins/jquery-slimscroll/jquery.slimscroll.js"></script>
   <script src="<?php echo BASE_URL; ?>admin/inc/assets/plugins/jquery.nicescroll/jquery.nicescroll.min.js"></script>

   <!--classic JS-->
   <script src="<?php echo BASE_URL; ?>admin/inc/assets/plugins/classie/classie.js"></script>

   <!-- notification -->
   <script src="<?php echo BASE_URL; ?>admin/inc/assets/plugins/notification/js/bootstrap-growl.min.js"></script>

   <!-- Sparkline charts -->
   <script src="<?php echo BASE_URL; ?>admin/inc/assets/plugins/jquery-sparkline/dist/jquery.sparkline.js"></script>

   <!-- Counter js  -->
   <script src="<?php echo BASE_URL; ?>admin/inc/assets/plugins/waypoints/jquery.waypoints.min.js"></script>
   <script src="<?php echo BASE_URL; ?>admin/inc/assets/plugins/countdown/js/jquery.counterup.js"></script>

   <!-- Echart js -->
   <script src="<?php echo BASE_URL; ?>admin/inc/assets/plugins/charts/echarts/js/echarts-all.js"></script>

   <script src="https://code.highcharts.com/highcharts.js"></script>
   <script src="https://code.highcharts.com/modules/exporting.js"></script>
   <script src="https://code.highcharts.com/highcharts-3d.js"></script>

   <!-- custom js -->
   <script type="text/javascript" src="<?php echo BASE_URL; ?>admin/inc/assets/js/main.min.js"></script>
   <script type="text/javascript" src="<?php echo BASE_URL; ?>admin/inc/assets/pages/dashboard.js"></script>
   <script type="text/javascript" src="<?php echo BASE_URL; ?>admin/inc/assets/pages/elements.js"></script>
   <script src="<?php echo BASE_URL; ?>admin/inc/assets/js/menu.min.js"></script>

   <!-- live search select2 -->
   <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


   <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
var $window = $(window);
var nav = $('.fixed-button');
$window.scroll(function(){
    if ($window.scrollTop() >= 200) {
       nav.addClass('active');
    }
    else {
       nav.removeClass('active');
    }
});
</script>

<?php if (!empty($dashboard_chart_data) && isset($dashboard_chart_data['totals'])): ?>
<script>
(function () {
    var stats = window.adminDashboardStats || {};
    var totals = stats.totals || {};
    var monthly = stats.monthly || {};
    var months = stats.months || [];

    if (typeof Highcharts === 'undefined') {
        return;
    }

    if (document.getElementById('barchart')) {
        Highcharts.chart('barchart', {
            chart: {
                type: 'column',
                backgroundColor: 'transparent',
                style: {
                    fontFamily: "'Inter', -apple-system, sans-serif"
                }
            },
            title: {
                text: '',
                style: { display: 'none' }
            },
            xAxis: {
                categories: months,
                labels: {
                    style: { color: '#64748b', fontWeight: '600' }
                },
                gridLineWidth: 0,
                lineColor: '#e2e8f0'
            },
            yAxis: {
                min: 0,
                allowDecimals: false,
                title: {
                    text: 'New Records',
                    style: { color: '#64748b', fontWeight: '600' }
                },
                gridLineColor: '#f1f5f9',
                labels: {
                    style: { color: '#64748b' }
                }
            },
            legend: {
                enabled: true,
                itemStyle: { color: '#475569', fontWeight: '600', fontSize: '0.75rem' }
            },
            tooltip: {
                shared: true,
                backgroundColor: 'rgba(255,255,255,0.95)',
                borderColor: '#e2e8f0',
                borderRadius: 12,
                borderWidth: 1,
                shadow: true,
                padding: 12
            },
            colors: ['#6366f1', '#22c55e', '#f59e0b', '#ef4444'],
            series: [
                { name: 'Doctors', data: monthly.doctors || [], borderRadius: 6 },
                { name: 'Hospitals', data: monthly.hospitals || [], borderRadius: 6 },
                { name: 'Labs', data: monthly.labs || [], borderRadius: 6 },
                { name: 'Blood Banks', data: monthly.bloodBanks || [], borderRadius: 6 }
            ],
            credits: { enabled: false }
        });
    }

    if (document.getElementById('piechart')) {
        Highcharts.chart('piechart', {
            chart: {
                type: 'pie',
                backgroundColor: 'transparent',
                style: {
                    fontFamily: "'Inter', -apple-system, sans-serif"
                }
            },
            title: {
                text: '',
                style: { display: 'none' }
            },
            tooltip: {
                backgroundColor: 'rgba(255,255,255,0.95)',
                borderColor: '#e2e8f0',
                borderRadius: 12,
                borderWidth: 1,
                shadow: true,
                padding: 12,
                pointFormat: '<span style="font-size:0.9rem; font-weight:600;">{point.y} ({point.percentage:.1f}%)</span>'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        format: '<b>{point.name}</b><br/>{point.y}',
                        style: {
                            color: '#0f172a',
                            fontWeight: '600',
                            fontSize: '0.7rem'
                        },
                        connectorColor: '#e2e8f0'
                    },
                    showInLegend: false,
                    size: '85%'
                }
            },
            colors: ['#6366f1', '#22c55e', '#f59e0b', '#ef4444'],
            series: [{
                name: 'Distribution',
                colorByPoint: true,
                data: [
                    { name: 'Doctors', y: totals.doctors || 0 },
                    { name: 'Hospitals', y: totals.hospitals || 0 },
                    { name: 'Labs', y: totals.labs || 0 },
                    { name: 'Blood Banks', y: totals.bloodBanks || 0 }
                ]
            }],
            credits: { enabled: false }
        });
    }
})();
</script>
<?php endif; ?>

</body>

</html>
