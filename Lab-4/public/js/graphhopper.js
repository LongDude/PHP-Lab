import L from 'https://esm.sh/leaflet@1.9.4';

// Глобальные переменные
window.routeData = {
    start: null,
    end: null,
    distance: null
};

let map;
let startMarker;
let endMarker;
let routeLayer;

var route_start;
var route_end;
var route_distance;
const urlParams = new URLSearchParams(window.location.search);

document.addEventListener('DOMContentLoaded', () => {
    // Инициализация карты
    
    map = L.map('map');
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    if (urlParams.has('viewState')){
        let viewState = JSON.parse(urlParams.get('viewState'));
        map.setView([viewState.center.lat, viewState.center.lng], viewState.zoom);
    } else {
        map.setView([52.6052, 39.5949], 13);
    }

    if (urlParams.has('firstPoint') && urlParams.get('firstPoint') != ''){
        let latLng = JSON.parse(urlParams.get('firstPoint'));
        setStartPoint(latLng); 
    }

    if (urlParams.has('lastPoint' && urlParams.get('lastPoint') != '')){
        let latLng = JSON.parse(urlParams.get('lastPoint'));
        setEndPoint(latLng); 
    }

    // Обработчик кликов по карте
    map.on('click', async (e) => {
        if (!route_start) {
            setStartPoint(e.latlng);
        } else if (!route_end) {
            setEndPoint(e.latlng);
            await calculateAndDrawRoute();
        } else {
            clearRoute();
        }
    });
});

function setStartPoint(latlng) {
    route_start = latlng;
    if (startMarker) map.removeLayer(startMarker);
    startMarker = L.marker(latlng, {
        icon: L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
            iconSize: [25, 41]
        })
    }).addTo(map);
}

function setEndPoint(latlng) {
    route_end = latlng;
    if (endMarker) map.removeLayer(endMarker);
    endMarker = L.marker(latlng, {
        icon: L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
            iconSize: [25, 41]
        })
    }).addTo(map);
}

async function calculateAndDrawRoute() {
    try {
        const response = await fetch(
            `http://localhost:8989/route?` +
            `point=${route_start.lat},${route_start.lng}&` +
            `point=${route_end.lat},${route_end.lng}&` +
            `profile=car&points_encoded=false`
        );
        
        const data = await response.json();
        
        if (data.paths?.[0]?.points?.coordinates) {
            route_distance = data.paths[0].distance;
            drawRoute(data.paths[0].points.coordinates);
        }
    } catch (error) {
        console.error('Ошибка:', error);
        alert('Ошибка построения маршрута!');
    }
}

function drawRoute(coordinates) {
    // Удаляем предыдущий маршрут
    if (routeLayer) map.removeLayer(routeLayer);
    
    // Конвертируем координаты [lng, lat] → [lat, lng]
    const latLngs = coordinates.map(c => [c[1], c[0]]);
    
    // Рисуем новый маршрут
    routeLayer = L.polyline(latLngs, {
        color: '#3388ff',
        weight: 5,
        opacity: 0.7
    }).addTo(map);

    // Центрируем карту на маршруте
    map.fitBounds(routeLayer.getBounds());
}

// Функция сброса маршрута
function clearRoute() {
    route_start = null
    route_end = null
    route_distance = null
    if (startMarker) map.removeLayer(startMarker);
    if (endMarker) map.removeLayer(endMarker);
    if (routeLayer) map.removeLayer(routeLayer);
}


// Form submission handler
document.getElementById('filter-orders').addEventListener('click', function(d) {
    d.preventDefault();

    const params = new URLSearchParams({
        viewState: JSON.stringify({center: map.getCenter(), zoom: map.getZoom()}),
        firstPoint: JSON.stringify(route_start??''),
        lastPoint: JSON.stringify(route_end??''),
        distance: JSON.stringify(route_distance??'')
    })

    const formData = new FormData(document.querySelector('form'));
    formData.forEach((val, name) => params.append(name, val));

    window.location.assign(`${window.location.pathname}?${params.toString()}`);
});


var selectedDriver = null;
var driver_id = null;
var tariff_id = null;
var price = null;
function handleDriverSelection(element) {
    if (selectedDriver === element) {
        submitDriverSelection();
        return;
    }
    
    if (selectedDriver) {
        selectedDriver.classList.remove('order-selected');
    }
    element.classList.add('order-selected');
    
    
    selectedDriver = element;
    driver_id = element.dataset.driverId;
    tariff_id = element.dataset.tariffId;
    price = element.dataset.price;
}

function submitDriverSelection() {
    if (!startMarker || !endMarker) {
        alert('Пожалуйста, выберите начальную и конечную точки на карте.');
        return;
    }
    
    // Get coordinates from map
    const beginCoords = startMarker.getLatLng();
    const endCoords = endMarker.getLatLng();
    
    // Get distance from routing control (if available)
    let distance = 0;
    const route = routingControl.getPlan();
    if (route) {
        distance = route.summary.totalDistance;
    } else {
        // Fallback to direct distance if no route
        distance = map.distance(beginCoords, endCoords);
    }
    
    // Prepare form data
    const formData = new FormData();
    formData.append('driver_id', driver_id);
    formData.append('tariff_id', tariff_id);
    formData.append('price', price);
    formData.append('begin', `${beginCoords.lng.toFixed(5)};${beginCoords.lat.toFixed(5)}`);
    formData.append('destination', `${endCoords.lng.toFixed(5)};${endCoords.lat.toFixed(5)}`);
    formData.append('distance', distance.toFixed(0));
    
    // Send AJAX request
    fetch('', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        // Handle successful response
        if (data.success) {
            console.log(data)
        } else {
            alert(data.message || 'Произошла ошибка');
        }
    })
    .catch(error => {
        alert('Ошибка соединения: ' + error.message);
    });
}
