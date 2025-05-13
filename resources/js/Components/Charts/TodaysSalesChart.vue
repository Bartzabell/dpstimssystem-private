<script>
import ApexCharts from 'apexcharts';

export default {
    data() {
        return {
            chart: null,
            isDarkMode: false,
            chartOptions: {
                chart: {
                    type: 'bar',
                    height: 350,
                    toolbar: {
                        show: false
                    },
                    foreColor: '#333333'
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '55%',
                        endingShape: 'rounded'
                    },
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                xaxis: {
                    categories: [],
                    title: {
                        text: 'Item Code'
                    }
                },
                yaxis: [
                    {
                        title: {
                            text: 'Quantity Sold'
                        },
                        min: 0
                    },
                    {
                        opposite: true,
                        title: {
                            text: 'Total Sales (₱)'
                        },
                        min: 0
                    }
                ],
                fill: {
                    opacity: 1
                },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return val.toFixed(2);
                        }
                    },
                    theme: 'light'
                },
                colors: ['#008453', '#00C853'],
                grid: {
                    borderColor: '#e7e7e7',
                },
                title: {
                    text: "Today's Sales by Item",
                    align: 'center',
                    margin: 20,
                    style: {
                        fontSize: '16px'
                    }
                },
                noData: {
                    text: 'Loading...'
                },
                legend: {
                    position: 'top'
                }
            }
        };
    },
    mounted() {
        this.checkDarkMode();
        this.fetchTodaysSalesData();
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', this.checkDarkMode);
    },
    methods: {
        checkDarkMode() {
            this.isDarkMode = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            this.updateChartTheme();
        },
        updateChartTheme() {
            if (this.isDarkMode) {
                this.chartOptions.chart.foreColor = '#f0f0f0';
                this.chartOptions.colors = ['#00e676', '#69f0ae'];
                this.chartOptions.grid.borderColor = '#888888';
                this.chartOptions.tooltip.theme = 'dark';
            } else {
                this.chartOptions.chart.foreColor = '#333333';
                this.chartOptions.colors = ['#008453', '#00C853'];
                this.chartOptions.grid.borderColor = '#e7e7e7';
                this.chartOptions.tooltip.theme = 'light';
            }

            if (this.chart) {
                this.chart.updateOptions(this.chartOptions);
            }
        },
        async fetchTodaysSalesData() {
            try {
                const response = await axios.get('/api/sales/todays-sales-by-item');
                const data = response.data;

                this.renderChart(data.items, data.quantities, data.sales);
            } catch (error) {
                console.error('Error fetching today\'s sales data:', error);
            }
        },
        renderChart(items, quantities, sales) {
            const series = [
                {
                    name: 'Quantity Sold',
                    data: quantities
                },
                {
                    name: 'Total Sales (₱)',
                    data: sales
                }
            ];

            const options = {
                ...this.chartOptions,
                xaxis: {
                    ...this.chartOptions.xaxis,
                    categories: items
                }
            };

            if (this.chart) {
                this.chart.updateOptions(options);
                this.chart.updateSeries(series);
            } else {
                this.chart = new ApexCharts(
                    document.querySelector('#todaysSalesChart'),
                    { ...options, series }
                );
                this.chart.render();
            }
        },
        refreshChart() {
            this.fetchTodaysSalesData();
        }
    },
    beforeDestroy() {
        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').removeEventListener('change', this.checkDarkMode);
        }

        if (this.chart) {
            this.chart.destroy();
        }
    }
}
</script>

<template>
    <div>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold" :class="{ 'text-white': isDarkMode }">Today's Sales by Item</h2>
            <button
                @click="refreshChart"
                class="px-3 py-1 text-white transition bg-blue-500 rounded hover:bg-blue-600"
                :class="{ 'bg-blue-600 hover:bg-blue-700': isDarkMode }"
            >
                Refresh
            </button>
        </div>
        <div id="todaysSalesChart"></div>
    </div>
</template>

<style>
/* Add any additional styles here */
</style>
