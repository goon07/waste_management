@extends('layouts.app')

@section('content')
<section class="py-20 bg-gray-100">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-gray-800 mb-6">Map View</h2>

        <!-- Filter Controls -->
        <div class="mb-4 flex flex-wrap gap-3">
            <label class="flex items-center space-x-2">
                <input type="checkbox" id="filter-payment" checked>
                <span>Payment</span>
            </label>
            <label class="flex items-center space-x-2">
                <input type="checkbox" id="filter-pickup" checked>
                <span>Pickup</span>
            </label>
            <label class="flex items-center space-x-2">
                <input type="checkbox" id="filter-issues" checked>
                <span>Issues</span>
            </label>
        </div>

        <!-- Map -->
        <div id="map" class="w-full h-[600px] rounded-xl shadow"></div>

        <!-- Legend -->
        <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="p-3 bg-white rounded shadow">🟢 Paid</div>
            <div class="p-3 bg-white rounded shadow">🔴 Unpaid</div>
            <div class="p-3 bg-white rounded shadow">🟡 Pending Pickup</div>
            <div class="p-3 bg-white rounded shadow">⚠️ Open Issues</div>
        </div>
    </div>
</section>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>

<script>
    const map = L.map('map');

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
    }).addTo(map);

    const residents = @json($mapData);

    const paymentLayer = L.layerGroup().addTo(map);
    const pickupLayer = L.layerGroup().addTo(map);
    const issuesLayer = L.layerGroup().addTo(map);

    function renderMarkers() {
        // Clear old markers
        paymentLayer.clearLayers();
        pickupLayer.clearLayers();
        issuesLayer.clearLayers();

        let bounds = [];

        residents.forEach(resident => {
            if (!resident.lat || !resident.lng) return; // skip invalid coords

            const payment = (resident.payment_status || "").toLowerCase();
            const pickup = (resident.pickup_status || "").toLowerCase();
            const issue = (resident.issue_status || "").toLowerCase();

            const popup = `
                <b>${resident.name}</b><br>
                Payment: ${resident.payment_status}<br>
                Pickup: ${resident.pickup_status}<br>
                Issues: ${resident.issue_status}
            `;

            let marker = null;

            // Payment markers
            if (document.getElementById('filter-payment').checked) {
                if (["paid", "completed", "yes"].includes(payment)) {
                    marker = L.circleMarker([resident.lat, resident.lng], {
                        radius: 8, color: 'green', fillColor: 'green', fillOpacity: 0.8
                    }).bindPopup(popup).addTo(paymentLayer);
                }
                if (["unpaid", "no"].includes(payment)) {
                    marker = L.circleMarker([resident.lat, resident.lng], {
                        radius: 8, color: 'red', fillColor: 'red', fillOpacity: 0.8
                    }).bindPopup(popup).addTo(paymentLayer);
                }
            }

            // Pickup markers
            if (document.getElementById('filter-pickup').checked && pickup === 'pending') {
                marker = L.circleMarker([resident.lat, resident.lng], {
                    radius: 8, color: 'orange', fillColor: 'orange', fillOpacity: 0.8
                }).bindPopup(popup).addTo(pickupLayer);
            }

            // Issue markers
            if (document.getElementById('filter-issues').checked && issue === 'open') {
                marker = L.circleMarker([resident.lat, resident.lng], {
                    radius: 8, color: 'yellow', fillColor: 'yellow', fillOpacity: 0.8
                }).bindPopup(popup).addTo(issuesLayer);
            }

            if (marker) bounds.push([resident.lat, resident.lng]);
        });

      // Recenter after rendering
if (bounds.length) {
    map.fitBounds(bounds, { padding: [50, 50] });

    // Force a closer zoom if it's too far out
    if (map.getZoom() > 14) {
        map.setZoom(14); // adjust this number (12–16 works well for cities)
    }
} else {
    map.setView([0, 0], 5); // fallback world view
}

    }

    // Initial render
    renderMarkers();

    // Re-render when filters change
    document.querySelectorAll('#filter-payment, #filter-pickup, #filter-issues').forEach(input => {
        input.addEventListener('change', renderMarkers);
    });
</script>



@endsection
