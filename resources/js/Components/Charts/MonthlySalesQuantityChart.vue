<template>
    <div>
      <div>
        <div>
          <label for="year-select" class="mx-2">Select Year:</label>
          <select id="year-select" class="rounded-full" v-model="selectedYear" @change="fetchMonthlySalesQuantityData">
            <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
          </select>
        </div>
      </div>
      <div>
        <div id="monthlySalesQuantityChart"></div>
      </div>
    </div>
  </template>

<script>
    import ApexCharts from 'apexcharts';
    import axios from 'axios';

    export default {
        data() {
        return {
            chart: null,
            selectedYear: new Date().getFullYear(),
            availableYears: [],
            monthlyData: [], // Will hold data for all items
            items: [], // List of items to display in the chart
            chartOptions: {
            chart: {
                type: 'line',
                height: 300,
                zoom: {
                enabled: false
                },
                toolbar: {
                show: true
                }
            },
            stroke: {
                curve: 'smooth',
                width: 2
            },
            colors: ['#008FFB', '#00E396', '#FEB019', '#FF4560', '#775DD0', '#546E7A', '#D4526E', '#8D5B4C', '#F86624'],
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
                text: 'Quantity Sold'
                },
                labels: {
                formatter: function(value) {
                    return value.toFixed(0);
                }
                }
            },
            tooltip: {
                y: {
                formatter: function(value) {
                    return value.toFixed(0);
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
                size: 4
            },
            title: {
                text: 'Monthly Sales Quantity by Item',
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

            this.fetchMonthlySalesQuantityData();
            } catch (error) {
            console.error('Error fetching available years:', error);
            }
        },
        async fetchMonthlySalesQuantityData() {
            try {
            const response = await axios.get(`/api/sales/monthly-sales-quantity/${this.selectedYear}`);
            this.monthlyData = response.data.monthlyData;
            this.items = response.data.items;
            this.renderChart();
            } catch (error) {
            console.error('Error fetching monthly sales quantity data:', error);
            }
        },
        renderChart() {
            const series = this.items.map(item => {
            return {
                name: item.name,
                data: this.monthlyData[item.id] || Array(12).fill(0) // Ensure 12 months of data
            };
            });

            if (this.chart) {
            this.chart.updateSeries(series);
            } else {
            this.chart = new ApexCharts(
                document.querySelector('#monthlySalesQuantityChart'),
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
    };
</script>
