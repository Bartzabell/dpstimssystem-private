<script>
    import ApexCharts from 'apexcharts';

    export default {
        data() {
            return {
                chart: null,
                selectedYear: new Date().getFullYear(),
                availableYears: [],
                monthlyData: [],
                isDarkMode: false,
                chartOptions: {
                    chart: {
                        type: 'line',
                        height: 300,
                        zoom: {
                            enabled: false
                        },
                        toolbar: {
                            show: false
                        },
                        foreColor: '#333333' // Default light mode text color
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 3
                    },
                    colors: ['#008453'], // Default light mode color
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
                            text: 'Total Sales Income'
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
                        },
                        theme: 'light' // Default light theme for tooltip
                    },
                    dataLabels: {
                        enabled: false
                    },
                    grid: {
                        borderColor: '#e7e7e7', // Default light mode grid color
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
            this.checkDarkMode();
            this.fetchAvailableYears();

            // Listen for changes to color scheme preference
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', this.checkDarkMode);
        },
        methods: {
            checkDarkMode() {
                this.isDarkMode = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                this.updateChartTheme();
            },
            updateChartTheme() {
                if (this.isDarkMode) {
                    // Dark mode settings
                    this.chartOptions.chart.foreColor = '#f0f0f0'; // Light text for dark mode
                    this.chartOptions.colors = ['#00e676']; // Lighter green for dark mode
                    this.chartOptions.grid.borderColor = '#888888'; // Darker grid for dark mode
                    this.chartOptions.tooltip.theme = 'dark'; // Dark tooltip
                } else {
                    // Light mode settings
                    this.chartOptions.chart.foreColor = '#333333'; // Dark text for light mode
                    this.chartOptions.colors = ['#008453']; // Original green
                    this.chartOptions.grid.borderColor = '#e7e7e7'; // Original grid color
                    this.chartOptions.tooltip.theme = 'light'; // Light tooltip
                }

                // If chart already exists, update it with new theme
                if (this.chart) {
                    this.chart.updateOptions(this.chartOptions);
                }
            },
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
                    const response = await axios.get(`/api/sales/monthly-income/${this.selectedYear}`, {
                        params: {
                            warehouse: this.$page.props.selectedWarehouse
                        }
                    });
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
            // Remove event listener when component is destroyed
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
        <div>
            <div>
                <label for="year-select" class="mx-2" :class="{ 'text-white': isDarkMode }">Select Year:</label>
                <select id="year-select" class="rounded-full" v-model="selectedYear" @change="fetchMonthlyIncomeData"
                        :class="{ 'bg-gray-800 text-white border-gray-700': isDarkMode }">
                    <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
                </select>
            </div>
        </div>
        <div>
            <div id="monthlyIncomeChart"></div>
        </div>
    </div>
</template>

<style>
/* Add dark mode CSS for select dropdown */
@media (prefers-color-scheme: dark) {
    select {
        background-color: #2d3748;
        color: white;
        border-color: #4a5568;
    }

    select option {
        background-color: #2d3748;
        color: white;
    }
}
</style>
