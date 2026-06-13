import Chart from 'chart.js/auto';

const dataEl = document.getElementById('health-overview-chart-data');

if (dataEl) {
    const charts = JSON.parse(dataEl.textContent);
    const brand = { lime: '#A4D400', teal: '#002B2B', gray: '#e5e7eb' };

    const baseOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                labels: {
                    boxWidth: 10,
                    padding: 14,
                    font: { size: 11 },
                    color: '#4b5563',
                },
            },
            tooltip: {
                backgroundColor: '#002B2B',
                titleFont: { size: 12 },
                bodyFont: { size: 11 },
                padding: 10,
                cornerRadius: 6,
            },
        },
    };

    const axisDefaults = {
        grid: { color: brand.gray, drawBorder: false },
        ticks: { font: { size: 11 }, color: '#6b7280' },
        border: { display: false },
    };

    const monthEl = document.getElementById('health-records-month-chart');
    if (monthEl && charts.recordsByMonth.values.some((value) => value > 0)) {
        new Chart(monthEl, {
            type: 'bar',
            data: {
                labels: charts.recordsByMonth.labels,
                datasets: [{
                    label: 'Records',
                    data: charts.recordsByMonth.values,
                    backgroundColor: brand.lime,
                    borderRadius: 6,
                    maxBarThickness: 36,
                }],
            },
            options: {
                ...baseOptions,
                plugins: {
                    ...baseOptions.plugins,
                    legend: { display: false },
                },
                scales: {
                    x: { ...axisDefaults, grid: { display: false } },
                    y: {
                        ...axisDefaults,
                        beginAtZero: true,
                        ticks: { ...axisDefaults.ticks, precision: 0 },
                    },
                },
            },
        });
    }

    const statusEl = document.getElementById('health-status-chart');
    if (statusEl && charts.animalsByStatus.values.length) {
        new Chart(statusEl, {
            type: 'doughnut',
            data: {
                labels: charts.animalsByStatus.labels,
                datasets: [{
                    data: charts.animalsByStatus.values,
                    backgroundColor: charts.animalsByStatus.colors,
                    borderWidth: 0,
                    hoverOffset: 4,
                }],
            },
            options: {
                ...baseOptions,
                cutout: '68%',
                layout: { padding: 4 },
                plugins: {
                    ...baseOptions.plugins,
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 10,
                            padding: 10,
                            font: { size: 10 },
                            color: '#4b5563',
                        },
                    },
                },
            },
        });
    }

    const typeEl = document.getElementById('health-records-type-chart');
    if (typeEl && charts.recordsByType.values.length) {
        new Chart(typeEl, {
            type: 'bar',
            data: {
                labels: charts.recordsByType.labels,
                datasets: [{
                    label: 'Records',
                    data: charts.recordsByType.values,
                    backgroundColor: brand.teal,
                    borderRadius: 4,
                    maxBarThickness: 18,
                }],
            },
            options: {
                indexAxis: 'y',
                ...baseOptions,
                plugins: {
                    ...baseOptions.plugins,
                    legend: { display: false },
                },
                scales: {
                    x: {
                        ...axisDefaults,
                        beginAtZero: true,
                        ticks: { ...axisDefaults.ticks, precision: 0 },
                    },
                    y: {
                        ...axisDefaults,
                        grid: { display: false },
                    },
                },
            },
        });
    }
}
