<script>
    import ApexCharts from 'apexcharts';

    export default {
        data() {
            return {
                chart: null,
                selectedYear: new Date().getFullYear(),
                availableYears: [],
                monthlyData: [],
                chartOptions: {
                chart: {
                    type: 'line',
                    height: 350,
                    zoom: {
                    enabled: false
                    },
                    toolbar: {
                    show: false
                    }
                },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                colors: ['#008453'],
                xaxis: {
                    categories: [
                    'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                    'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
                    ],
                    title: {
                    text: 'Month'
                    }
                },
                yaxis: {
                    title: {
                    text: 'Total Income'
                    },
                    labels: {
                    formatter: function(value) {
                        return '₱' + value.toFixed(2);
                    }
                    }
                },
                tooltip: {
                    y: {
                    formatter: function(value) {
                        return '₱' + value.toFixed(2);
                    }
                    }
                },
                dataLabels: {
                    enabled: false
                },
                grid: {
                    borderColor: '#e7e7e7',
                },
                markers: {
                    size: 5
                },
                title: {
                    text: 'Monthly Income',
                    align: 'center'
                },
                noData: {
                    text: 'Loading...'
                }
                }
            };
        },
        mounted() {
            this.fetchAvailableYears();
        },
        methods: {
            async fetchAvailableYears() {
                try {
                    const response = await axios.get('/api/sales/available-years');
                    this.availableYears = response.data;

                if (this.availableYears.length > 0) {
                    // Set the most recent year as default
                    this.selectedYear = this.availableYears[this.availableYears.length - 1];
                }

                this.fetchMonthlyIncomeData();
                } catch (error) {
                    console.error('Error fetching available years:', error);
                }
            },
            async fetchMonthlyIncomeData() {
                try {
                    const response = await axios.get(`/api/sales/monthly-income/${this.selectedYear}`);
                    this.monthlyData = response.data;
                    this.renderChart();
                } catch (error) {
                    console.error('Error fetching monthly income data:', error);
                }
            },
            renderChart() {
                const series = [{
                    name: 'Total Income',
                    data: this.monthlyData
                }];

                if (this.chart) {
                    this.chart.updateSeries(series);
                } else {
                    this.chart = new ApexCharts(
                        document.querySelector('#monthlyIncomeChart'),
                        { ...this.chartOptions, series }
                    );
                    this.chart.render();
                }
            }
        },
        beforeDestroy() {
            if (this.chart) {
                this.chart.destroy();
            }
        }
    }
</script>

<template>
    <div>
        <div>
            <h4>Monthly Income</h4>
            <div>
                <select v-model="selectedYear" @change="fetchMonthlyIncomeData">
                    <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
                </select>
            </div>
        </div>
        <div>
            <div id="monthlyIncomeChart"></div>
        </div>
    </div>
</template>
