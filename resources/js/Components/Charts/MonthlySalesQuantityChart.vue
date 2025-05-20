<template>
    <div>
      <div>
        <div>
          <label for="year-select" class="mx-2" :class="{ 'text-white': isDarkMode }">Select Year:</label>
          <select
            id="year-select"
            class="rounded-full"
            v-model="selectedYear"
            @change="fetchMonthlySalesQuantityData"
            :class="{ 'bg-gray-800 text-white border-gray-700': isDarkMode }">
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
    import { ref, watch, computed } from 'vue';

    export default {
      data() {
        return {
          chart: null,
          selectedYear: new Date().getFullYear(),
          availableYears: [],
          monthlyData: [], // Will hold data for all items
          items: [], // List of items to display in the chart
          isDarkMode: false,
          chartOptions: {
            chart: {
              type: 'line',
              height: 300,
              zoom: {
                enabled: false
              },
              toolbar: {
                show: true
              },
              foreColor: '#333333' // Default light mode text color
            },
            stroke: {
              curve: 'smooth',
              width: 2
            },
            // Light mode colors - will update these for dark mode
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
              size: 4
            },
            title: {
              text: 'Monthly Sales Quantity by Item',
              align: 'center'
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
            // Brighter colors for dark mode visibility
            this.chartOptions.colors = [
              '#00B0FF', // Brighter blue
              '#00E676', // Brighter green
              '#FFAB40', // Brighter orange
              '#FF5252', // Brighter red
              '#B39DDB', // Brighter purple
              '#90A4AE', // Brighter gray-blue
              '#FF80AB', // Brighter pink
              '#BCAAA4', // Brighter brown
              '#FFB74D'  // Brighter amber
            ];
            this.chartOptions.grid.borderColor = '#888888'; // Darker grid for dark mode
            this.chartOptions.tooltip.theme = 'dark'; // Dark tooltip
          } else {
            // Light mode settings
            this.chartOptions.chart.foreColor = '#333333'; // Dark text for light mode
            // Original colors
            this.chartOptions.colors = ['#008FFB', '#00E396', '#FEB019', '#FF4560', '#775DD0', '#546E7A', '#D4526E', '#8D5B4C', '#F86624'];
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

            this.fetchMonthlySalesQuantityData();
          } catch (error) {
            console.error('Error fetching available years:', error);
          }
        },
        async fetchMonthlySalesQuantityData() {
          try {
            const response = await axios.get(`/api/sales/monthly-sales-quantity/${this.selectedYear}`, {
                params: {
                    warehouse: this.$page.props.selectedWarehouse
                }
            });
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
        // Remove event listener when component is destroyed
        if (window.matchMedia) {
          window.matchMedia('(prefers-color-scheme: dark)').removeEventListener('change', this.checkDarkMode);
        }

        if (this.chart) {
          this.chart.destroy();
        }
      }
    };
  </script>

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

    /* ApexCharts toolbar adjustments for dark mode */
    .apexcharts-toolbar {
      filter: invert(0.8);
    }
  }
  </style>
