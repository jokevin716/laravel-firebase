@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
        <p class="mt-2 text-gray-600">Welcome back, {{ Auth::user()->name }}!</p>
    </div>

    @if(isset($error))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <strong>Error:</strong> {{ $error }}
        </div>
    @endif

    <!-- Stock Data Controls -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">Stock Market Data</h2>

        <div class="flex flex-wrap items-center gap-4 mb-4">
            <div>
                <label for="stock-symbols" class="block text-sm font-medium text-gray-700">Stock Symbols</label>
                <input type="text" id="stock-symbols" value="AAPL,GOOGL,MSFT" placeholder="Enter symbols separated by commas"
                       class="mt-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>
            <button onclick="refreshStockData()" class="mt-6 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                Refresh Data
            </button>
        </div>

        <div id="loading" class="hidden text-center py-4">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <p class="mt-2 text-gray-600">Loading stock data...</p>
        </div>
    </div>

    <!-- Charts Container -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Stock Price Trends</h3>
            <canvas id="priceChart" width="400" height="200"></canvas>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Trading Volume</h3>
            <canvas id="volumeChart" width="400" height="200"></canvas>
        </div>
    </div>

    <!-- Stock Data Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold">Latest Stock Data</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200" id="stockTable">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Symbol</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Open</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">High</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Low</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Close</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Volume</th>
                    </tr>
                </thead>
                <tbody id="stockTableBody" class="bg-white divide-y divide-gray-200">
                    <!-- Data will be populated by JavaScript -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
let priceChart = null;
let volumeChart = null;

// Initialize with server data if available
@if(isset($stockData) && $stockData)
    const initialStockData = @json($stockData);
    updateCharts(initialStockData);
    updateTable(initialStockData);
@endif

async function refreshStockData() {
    const symbols = document.getElementById('stock-symbols').value;
    const loading = document.getElementById('loading');

    loading.classList.remove('hidden');

    try {
        const response = await fetch(`{{ route('stock.data') }}?symbols=${encodeURIComponent(symbols)}`);
        const data = await response.json();

        if(response.ok) {
            updateCharts(data);
            updateTable(data);
        }
        else {
            alert('Error: ' + data.error);
        }
    }
    catch(error) {
        alert('Failed to fetch stock data');
        console.error('Error:', error);
    }
    finally {
        loading.classList.add('hidden');
    }
}

function updateCharts(data) {
    if(!data || !data.data || data.data.length === 0) {
        return;
    }

    // Group data by symbol
    const groupedData = {};
    data.data.forEach(item => {
        if(!groupedData[item.symbol]) {
            groupedData[item.symbol] = [];
        }

        groupedData[item.symbol].push(item);
    });

    // Prepare data for charts
    const symbols = Object.keys(groupedData);
    const colors = ['#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#8B5CF6'];

    // Price Chart
    const priceCtx = document.getElementById('priceChart').getContext('2d');

    if(priceChart) {
        priceChart.destroy();
    }

    const priceDatasets = symbols.map((symbol, index) => {
        const stockData = groupedData[symbol].sort((a, b) => new Date(a.date) - new Date(b.date));

        return {
            label: symbol,
            data: stockData.map(item => ({
                x: new Date(item.date),
                y: item.close
            })),
            borderColor: colors[index % colors.length],
            backgroundColor: colors[index % colors.length] + '20',
            tension: 0.1
        };
    });

    priceChart = new Chart(priceCtx, {
        type: 'line',
        data: {
            datasets: priceDatasets
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    type: 'time',
                    time: {
                        parser: 'yyyy-MM-dd',
                        displayFormats: {
                            day: 'MMM dd'
                        }
                    }
                },
                y: {
                    beginAtZero: false,
                    title: {
                        display: true,
                        text: 'Price ($)'
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Stock Price Trends'
                },
                legend: {
                    display: true
                }
            }
        }
    });

    // Volume Chart
    const volumeCtx = document.getElementById('volumeChart').getContext('2d');

    if(volumeChart) {
        volumeChart.destroy();
    }

    const volumeDatasets = symbols.map((symbol, index) => {
        const stockData = groupedData[symbol].sort((a, b) => new Date(a.date) - new Date(b.date));

        return {
            label: symbol,
            data: stockData.map(item => ({
                x: new Date(item.date),
                y: item.volume
            })),
            backgroundColor: colors[index % colors.length] + '60',
            borderColor: colors[index % colors.length],
            borderWidth: 1
        };
    });

    volumeChart = new Chart(volumeCtx, {
        type: 'bar',
        data: {
            datasets: volumeDatasets
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    type: 'time',
                    time: {
                        parser: 'yyyy-MM-dd',
                        displayFormats: {
                            day: 'MMM dd'
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Volume'
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Trading Volume'
                },
                legend: {
                    display: true
                }
            }
        }
    });
}

function updateTable(data) {
    const tableBody = document.getElementById('stockTableBody');

    if(!data || !data.data || data.data.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">No data available</td></tr>';
        return;
    }

    // Sort by date (newest first) and take latest entries
    const sortedData = data.data.sort((a, b) => new Date(b.date) - new Date(a.date));
    const latestData = sortedData.slice(0, 20); // Show latest 20 records

    tableBody.innerHTML = latestData.map(item => `
        <tr class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${item.symbol}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${new Date(item.date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${parseFloat(item.open).toFixed(2)}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${parseFloat(item.high).toFixed(2)}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${parseFloat(item.low).toFixed(2)}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${parseFloat(item.close).toFixed(2)}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${parseInt(item.volume).toLocaleString()}</td>
        </tr>
    `).join('');
}

// Add Chart.js time adapter
Chart.register({
    id: 'time',
    beforeInit: function(chart) {
        if(!Chart._adapters._date) {
            Chart._adapters._date = {
                parse: function(value) {
                    return new Date(value);
                },
                format: function(value, format) {
                    return new Date(value).toLocaleDateString();
                }
            };
        }
    }
});
</script>
@endsection
