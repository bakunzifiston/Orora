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

    // Shows the numbers on the doughnut: total in the centre, a count on each slice
    // big enough to hold it, and "label · count (percent)" in the legend.
    const doughnutNumbers = {
        id: 'doughnutNumbers',
        afterDatasetsDraw(chart) {
            const { ctx } = chart;
            const meta = chart.getDatasetMeta(0);
            const values = chart.data.datasets[0].data.map(Number);
            const total = values.reduce((sum, v) => sum + v, 0);
            if (!total || !meta?.data?.length) return;

            ctx.save();
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';

            meta.data.forEach((arc, index) => {
                if (chart.getDataVisibility && !chart.getDataVisibility(index)) return;
                const value = values[index];
                if (!value || value / total < 0.06) return;
                const { x, y } = arc.tooltipPosition();
                ctx.font = '700 11px system-ui, sans-serif';
                ctx.fillStyle = '#ffffff';
                ctx.fillText(String(value), x, y);
            });

            const first = meta.data[0];
            ctx.fillStyle = '#002B2B';
            ctx.font = '800 22px system-ui, sans-serif';
            ctx.fillText(total.toLocaleString(), first.x, first.y - 7);
            ctx.fillStyle = '#6b7280';
            ctx.font = '600 10px system-ui, sans-serif';
            const unit = chart.options.plugins.doughnutNumbers?.unit ?? ['animal', 'animals'];
            ctx.fillText(total === 1 ? unit[0] : unit[1], first.x, first.y + 12);
            ctx.restore();
        },
    };

    // Legend as page content beside the ring: label, count and percentage; click hides a slice.
    const renderLegend = (chart, canvasId) => {
        const list = document.querySelector(`[data-legend-for="${canvasId}"]`);
        if (!list) return;

        const values = chart.data.datasets[0].data.map(Number);
        const total = values.reduce((sum, v) => sum + v, 0);
        const colors = chart.data.datasets[0].backgroundColor;
        list.innerHTML = '';

        chart.data.labels.forEach((label, index) => {
            const value = values[index] ?? 0;
            const percent = total ? Math.round((value / total) * 100) : 0;
            const item = document.createElement('li');
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'health-donut__item';
            button.setAttribute('aria-pressed', 'true');
            button.innerHTML = `<span class="health-donut__swatch"></span><span class="health-donut__name"></span><span class="health-donut__count">${value.toLocaleString()}<span>${percent}%</span></span>`;
            button.querySelector('.health-donut__swatch').style.background = Array.isArray(colors) ? colors[index] : colors;
            button.querySelector('.health-donut__name').textContent = label;
            button.addEventListener('click', () => {
                chart.toggleDataVisibility(index);
                chart.update();
                const visible = chart.getDataVisibility(index);
                button.classList.toggle('is-hidden', !visible);
                button.setAttribute('aria-pressed', visible ? 'true' : 'false');
            });
            item.appendChild(button);
            list.appendChild(item);
        });
    };

    const statusEl = document.getElementById('health-status-chart');
    if (statusEl && charts.animalsByStatus.values.length) {
        const statusTotal = charts.animalsByStatus.values.reduce((sum, v) => sum + Number(v), 0);

        const statusChart = new Chart(statusEl, {
            plugins: [doughnutNumbers],
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
                    legend: { display: false },
                    tooltip: {
                        ...baseOptions.plugins.tooltip,
                        callbacks: {
                            label(context) {
                                const value = Number(context.parsed);
                                const percent = statusTotal ? Math.round((value / statusTotal) * 100) : 0;

                                return ` ${context.label}: ${value} (${percent}%)`;
                            },
                        },
                    },
                },
            },
        });

        renderLegend(statusChart, 'health-status-chart');
    }

    const typeEl = document.getElementById('health-records-type-chart');
    if (typeEl && charts.recordsByType.values.length) {
        const typeTotal = charts.recordsByType.values.reduce((sum, v) => sum + Number(v), 0);
        const typePalette = ['#002B2B', '#A4D400', '#3b82f6', '#f59e0b', '#8b5cf6', '#ef4444', '#14b8a6', '#9ca3af'];

        const typeChart = new Chart(typeEl, {
            type: 'doughnut',
            plugins: [doughnutNumbers],
            data: {
                labels: charts.recordsByType.labels,
                datasets: [{
                    data: charts.recordsByType.values,
                    backgroundColor: charts.recordsByType.values.map((_, i) => typePalette[i % typePalette.length]),
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
                    doughnutNumbers: { unit: ['record', 'records'] },
                    legend: { display: false },
                    tooltip: {
                        ...baseOptions.plugins.tooltip,
                        callbacks: {
                            label(context) {
                                const value = Number(context.parsed);
                                const percent = typeTotal ? Math.round((value / typeTotal) * 100) : 0;

                                return ` ${context.label}: ${value} (${percent}%)`;
                            },
                        },
                    },
                },
            },
        });

        renderLegend(typeChart, 'health-records-type-chart');
    }
}
