@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('content')
    <h1 class="dash-welcome">Welcome, {{ auth()->user()->name }}</h1>

    <div class="dash-stats">
        @foreach ($stats as $stat)
            <div class="dash-stat-card">
                <div>
                    <div class="dash-stat-label">{{ $stat['label'] }}</div>
                    <div class="dash-stat-value @if (! empty($stat['highlight'])) accent @elseif (! empty($stat['alert'])) alert @endif">
                        {{ $stat['value'] }}
                        @if (! empty($stat['suffix']))
                            <span style="font-size: 0.875rem; font-weight: 500; color: #6b7280;">{{ $stat['suffix'] }}</span>
                        @endif
                    </div>
                </div>
                @include('modules.partials.stat-icon', ['icon' => $stat['icon'] ?? 'grid'])
            </div>
        @endforeach
    </div>

    <div class="dash-grid">
        <div>
            <div class="dash-panel" style="margin-bottom: 1.25rem;">
                <div class="dash-panel-title">Overview Map</div>
                <div class="dash-map" aria-hidden="true">
                    <span class="dash-map-pin" style="top: 35%; left: 48%;"></span>
                    <span class="dash-map-pin" style="top: 52%; left: 55%;"></span>
                    <span class="dash-map-pin" style="top: 42%; left: 42%;"></span>
                    <span class="dash-map-pin" style="top: 60%; left: 50%;"></span>
                </div>
            </div>

            <div class="dash-grid-2">
                <div class="dash-panel">
                    <div class="dash-panel-title">Traceability</div>
                    <div class="dash-module-card">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <strong style="font-size: 0.875rem;">BP-4692</strong>
                            <span class="dash-badge-green">In transit</span>
                        </div>
                        <p style="font-size: 0.75rem; color: #808080; margin-bottom: 0.75rem;">Origin → Transit → Delivery</p>
                        <div style="height: 6px; background: #e5e7eb; border-radius: 9999px; overflow: hidden;">
                            <div style="width: 65%; height: 100%; background: #A4D400; border-radius: 9999px;"></div>
                        </div>
                    </div>
                    @foreach ($modules as $module)
                        <div class="dash-module-card">
                            <strong style="font-size: 0.875rem;">{{ $module['title'] }}</strong>
                            <p style="font-size: 0.75rem; color: #808080; margin-top: 0.25rem;">{{ $module['description'] }}</p>
                            <span class="dash-badge-orange" style="margin-top: 0.5rem; display: inline-block;">Coming soon</span>
                        </div>
                    @endforeach
                </div>

                <div class="dash-panel">
                    <div class="dash-panel-title">Inventory Overview</div>
                    <div class="dash-module-card">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong style="font-size: 0.8125rem;">Beef Tenderloin</strong>
                                <p style="font-size: 0.75rem; color: #808080;">1,240 kg · -2°C</p>
                            </div>
                            <span class="dash-badge-green">In Compliance</span>
                        </div>
                    </div>
                    <div class="dash-module-card">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong style="font-size: 0.8125rem;">Goat Ribs</strong>
                                <p style="font-size: 0.75rem; color: #808080;">680 kg · +4°C</p>
                            </div>
                            <span class="dash-badge-orange">Near Threshold</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="dash-panel" style="margin-bottom: 1.25rem;">
                <div class="dash-panel-title">Shipment Status</div>
                <div class="dash-donut">
                    <div class="dash-donut-chart" aria-hidden="true"></div>
                    <div class="dash-legend">
                        <span><i style="background: #A4D400;"></i> Delivered</span>
                        <span><i style="background: #e5e7eb;"></i> Processing</span>
                        <span><i style="background: #9ca3af;"></i> Delayed</span>
                    </div>
                </div>
            </div>

            <div class="dash-panel" style="margin-bottom: 1.25rem;">
                <div class="dash-panel-title">Compliance Score</div>
                <div class="dash-gauge" aria-hidden="true"></div>
                <p style="text-align: center; font-weight: 700; font-size: 1.125rem; color: #A4D400;">A+</p>
            </div>

            <div class="dash-panel">
                <div class="dash-panel-title">Alerts</div>
                <div class="dash-alert-item">
                    <span style="color: #16a34a; font-weight: 600;">+6.5°C</span>
                    <div>
                        <strong>Temperature Alert</strong>
                        <p style="color: #808080; font-size: 0.75rem;">Cold chain within range</p>
                    </div>
                </div>
                <div class="dash-alert-item">
                    <span style="color: #dc2626; font-weight: 600;">+4.3°C</span>
                    <div>
                        <strong>Labeling Issue</strong>
                        <p style="color: #808080; font-size: 0.75rem;">Review required — Zone B</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
