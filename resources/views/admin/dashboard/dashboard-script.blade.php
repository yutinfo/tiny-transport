<script>
    $(function() {
        $('#select_province').select2();
        $('input[name="select_date"]').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            minYear: 1901,
            autoApply: true,
            maxDate: new Date(),
            locale: {
                format: 'DD/MM/YYYY'
            },
            maxYear: parseInt(moment().format('YYYY'), 10)
        }, function(start, end, label) {
            $('input[name="select_date"]').val(start.format('YYYY-MM-DD'))
            $('input[name="db_date"]').val(start.format('YYYY/MM/DD'))

        });

        const orderTable = $("#order_table");

        if (orderTable.length && !$.fn.DataTable.isDataTable(orderTable)) {
            const reportTable = orderTable.DataTable({
                "ordering": false,
                "responsive": true,
                "searching": false,
                "lengthChange": true,
                "autoWidth": false,
                "dom": 'Bfrtip',
                "language": {
                    "emptyTable": "ไม่พบข้อมูลรายงานพัสดุตามตัวกรองที่เลือก",
                    "info": "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
                    "infoEmpty": "ไม่มีข้อมูล",
                    "lengthMenu": "แสดง _MENU_ รายการ",
                    "paginate": {
                        "previous": "ก่อนหน้า",
                        "next": "ถัดไป"
                    }
                },
                "buttons": [{
                        extend: 'csv',
                        charset: 'utf-8',
                    },{
                        extend: 'excel',
                        text: 'Excel',

                    },
                    {
                        extend: 'print',
                        charset: 'utf-8',
                    }
                ],

            });

            reportTable.buttons()
                .container()
                .appendTo('#order_table_wrapper .col-md-6:eq(0)');
        }


        // Chart.js Data & Configurations
        const dailyTrendData = @json($dailyTrend);
        const deliveryBreakdown = @json($deliveryBreakdown);
        const codSummary = @json($codSummary);
        const tripsByStatus = @json($tripsByStatus);
        
        const deliveryStatusLabels = @json($deliveryStatusLabels);
        const tripStatusLabels = @json($tripStatusLabels);

        const themeColors = {
            blue: '#2563eb',
            teal: '#22D3EE',
            green: '#16a34a',
            orange: '#d97706',
            red: '#dc2626',
            gray: '#64748b'
        };

        const deliveryColors = {
            waiting: themeColors.gray,
            picked_up: themeColors.teal,
            in_transit: themeColors.blue,
            delivered: themeColors.green,
            failed: themeColors.red,
            returned: themeColors.orange
        };

        const tripColors = {
            draft: themeColors.gray,
            assigned: themeColors.teal,
            in_transit: themeColors.blue,
            completed: themeColors.green,
            cancelled: themeColors.red
        };

        // 1. Delivery Trend Chart (Line Chart)
        const trendDates = dailyTrendData.map(d => moment(d.date).format('DD/MM'));
        const trendTotal = dailyTrendData.map(d => d.total_items);
        const trendDelivered = dailyTrendData.map(d => d.delivered_items);

        const ctxTrend = document.getElementById('deliveryTrendChart').getContext('2d');
        new Chart(ctxTrend, {
            type: 'line',
            data: {
                labels: trendDates.length ? trendDates : ['ไม่มีข้อมูล'],
                datasets: [
                    {
                        label: 'พัสดุเข้ารอบรวม',
                        data: trendTotal.length ? trendTotal : [0],
                        borderColor: themeColors.blue,
                        backgroundColor: 'rgba(37, 99, 235, 0.05)',
                        borderWidth: 2,
                        pointRadius: 3,
                        lineTension: 0.15
                    },
                    {
                        label: 'ส่งสำเร็จ',
                        data: trendDelivered.length ? trendDelivered : [0],
                        borderColor: themeColors.green,
                        backgroundColor: 'rgba(22, 163, 74, 0.05)',
                        borderWidth: 2,
                        pointRadius: 3,
                        lineTension: 0.15
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: 'top',
                    labels: {
                        boxWidth: 12,
                        fontFamily: 'inherit',
                        fontStyle: 'bold'
                    }
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            precision: 0
                        }
                    }]
                }
            }
        });

        // 2. Delivery Status Chart (Doughnut Chart)
        const statusLabels = Object.values(deliveryStatusLabels);
        const statusData = Object.keys(deliveryStatusLabels).map(k => deliveryBreakdown[k] || 0);
        const statusColors = Object.keys(deliveryStatusLabels).map(k => deliveryColors[k] || themeColors.gray);

        const ctxStatus = document.getElementById('deliveryStatusChart').getContext('2d');
        new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusData.reduce((a, b) => a + b, 0) > 0 ? statusData : [1],
                    backgroundColor: statusData.reduce((a, b) => a + b, 0) > 0 ? statusColors : ['#e2e8f0'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    display: false
                },
                cutoutPercentage: 65
            }
        });

        // 3. COD Summary Chart (Bar Chart)
        const ctxCod = document.getElementById('codChart').getContext('2d');
        new Chart(ctxCod, {
            type: 'bar',
            data: {
                labels: ['ยอดรวม', 'เก็บแล้ว', 'คงเหลือ'],
                datasets: [{
                    data: [
                        codSummary.total_cod_amount || 0,
                        codSummary.collected_amount || 0,
                        codSummary.remaining_cod_amount || 0
                    ],
                    backgroundColor: [themeColors.blue, themeColors.green, themeColors.red],
                    barPercentage: 0.5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    display: false
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                }
            }
        });

        // 4. Trips Status Chart (Bar Chart)
        const tripLabelsArr = Object.values(tripStatusLabels);
        const tripDataArr = Object.keys(tripStatusLabels).map(k => tripsByStatus[k] || 0);
        const tripColorsArr = Object.keys(tripStatusLabels).map(k => tripColors[k] || themeColors.gray);

        const ctxTrips = document.getElementById('tripsChart').getContext('2d');
        new Chart(ctxTrips, {
            type: 'bar',
            data: {
                labels: tripLabelsArr,
                datasets: [{
                    data: tripDataArr,
                    backgroundColor: tripColorsArr,
                    barPercentage: 0.5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    display: false
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            precision: 0
                        }
                    }]
                }
            }
        });
    });
</script>
