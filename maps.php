<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Maps Modal</title>
    
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .map-container {
            height: 50vh;
            width: 100%;
        }
    </style>
</head>
<body class="d-flex justify-content-center align-items-center" style="height: 100vh;">
    <div class="container align-items-center">
        <div class="d-flex mb-3 align-items-center">
            <div class="mr-2">
                <h5>Address 1:</h5>
            </div>
            <div class="mr-2">
                <input id="searchBox1" class="form-control" placeholder="Enter a location" style="width: 250px;" list="address1-list">
                <datalist id="address1-list"></datalist>
            </div>
            <div>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#map1Modal">
                    Choose From Map
                </button>
            </div>
        </div>

        <div class="d-flex mb-3 align-items-center">
            <div class="mr-2">
                <h5>Address 2:</h5>
            </div>
            <div class="mr-2">
                <input id="searchBox2" class="form-control" placeholder="Enter a location" style="width: 250px;" list="address2-list">
                <datalist id="address2-list"></datalist>
            </div>
            <div>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#map2Modal">
                    Choose From Map
                </button>
            </div>
        </div>

        <div class="mb-3 align-items-center">
            <button type="button" class="btn btn-primary" data-toggle="modal" onclick="calculateDistance()">
                Get Distance
            </button>
        </div>

        <div class="d-flex mb-3 align-items-center">
            <div class="mr-2">
                <h5>Distance:</h5>
            </div>
            <div>
                <span id="distance">0.00</span> Miles
            </div>
        </div>

        <div class="d-flex mb-3 align-items-center">
            <div class="mr-2">
                <h5>Amount Per Mile:</h5>
            </div>
            <div class="mr-2">
                <input id="amountPerMile" class="form-control" placeholder="Enter amount per mile" style="width: 250px;">
            </div>
        </div>

        <div class="d-flex mb-3 align-items-center">
            <div class="mr-2">
                <h5>Shipping Amount:</h5>
            </div>
            <div class="mr-2">
                <span id="shippingAmount">$0.00</span>
            </div>
        </div>
    </div>

    <div class="modal fade" id="map1Modal" tabindex="-1" role="dialog" aria-labelledby="mapsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mapsModalLabel">Select Starting Point</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="map1" class="map-container"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="map2Modal" tabindex="-1" role="dialog" aria-labelledby="mapsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mapsModalLabel">Select End Point</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="map2" class="map-container"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    
    <script>
        let map1, map2;
        let marker1, marker2;
        let lat1 = lng1 = lat2 = lng2 = 0;

        $('#amountPerMile').on('input', function() {
            var distance = parseFloat($('#distance').text());
            var amountPerMile = parseFloat($(this).val());

            if (isNaN(distance)) {
                distance = 0;
            }

            if (!isNaN(amountPerMile)) {
                var shippingAmount = distance * amountPerMile;
                $('#shippingAmount').text('$' + shippingAmount.toFixed(2));
            } else {
                $('#shippingAmount').text('$0.00');
            }
        });

        $('#searchBox1').on('input', function() {
            updateSuggestions('#searchBox1', '#address1-list');
        });

        $('#searchBox2').on('input', function() {
            updateSuggestions('#searchBox2', '#address2-list');
        });

        function updateSuggestions(inputId, listId) {
            var query = $(inputId).val();
            if (query.length >= 2) {
                $.ajax({
                    url: `https://nominatim.openstreetmap.org/search`,
                    data: {
                        q: query,
                        format: 'json',
                        addressdetails: 1,
                        limit: 5
                    },
                    dataType: 'json',
                    success: function(data) {
                        var datalist = $(listId);
                        datalist.empty();
                        data.forEach(function(item) {
                            var option = $('<option>').attr('value', item.display_name).data('lat', item.lat).data('lon', item.lon);
                            datalist.append(option);
                        });
                    }
                });
            }
        }

        function getPlaceName(lat, lng, inputId) {
            const url = `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json&addressdetails=1`;

            $.ajax({
                url: url,
                dataType: 'json',
                success: function(data) {
                    if (data && data.display_name) {
                        $(inputId).val(data.display_name);
                    } else {
                        console.error("Address not found for these coordinates.");
                        $(inputId).val("Address not found");
                    }
                },
                error: function() {
                    console.error("Error retrieving address from Nominatim.");
                    $(inputId).val("Error retrieving address");
                }
            });
        }

        $('#searchBox1').on('change', function() {
            let selectedOption = $('#address1-list option[value="' + $(this).val() + '"]');
            lat1 = parseFloat(selectedOption.data('lat'));
            lng1 = parseFloat(selectedOption.data('lon'));
            updateMarker(map1, marker1, lat1, lng1, "Starting Point");
        });

        $('#searchBox2').on('change', function() {
            let selectedOption = $('#address2-list option[value="' + $(this).val() + '"]');
            lat2 = parseFloat(selectedOption.data('lat'));
            lng2 = parseFloat(selectedOption.data('lon'));
            updateMarker(map2, marker2, lat2, lng2, "End Point");
        });

        function updateMarker(map, marker, lat, lng, title) {
            if (!map) return;
            
            const position = new google.maps.LatLng(lat, lng);

            if (marker) {
                marker.setPosition(position);
            } else {
                marker = new google.maps.Marker({
                    position: position,
                    map: map,
                    title: title
                });
            }
            map.setCenter(position);
        }

        function initMaps() {
            map1 = new google.maps.Map(document.getElementById("map1"), {
                center: { lat: 40.7128, lng: -74.0060 },
                zoom: 10,
            });

            map2 = new google.maps.Map(document.getElementById("map2"), {
                center: { lat: 34.0522, lng: -118.2437 },
                zoom: 10,
            });

            google.maps.event.addListener(map1, 'click', function(event) {
                lat1 = event.latLng.lat();
                lng1 = event.latLng.lng();
                if (marker1) {
                    marker1.setMap(null);
                }
                marker1 = new google.maps.Marker({
                    position: event.latLng,
                    map: map1,
                    title: "Starting Point",
                });

                getPlaceName(lat1, lng1, '#searchBox1');
            });

            google.maps.event.addListener(map2, 'click', function(event) {
                lat2 = event.latLng.lat();
                lng2 = event.latLng.lng();

                if (marker2) {
                    marker2.setMap(null);
                }

                marker2 = new google.maps.Marker({
                    position: event.latLng,
                    map: map2,
                    title: "End Point",
                });

                getPlaceName(lat2, lng2, '#searchBox2');
            });
        }

        function loadGoogleMapsAPI() {
            const script = document.createElement('script');
            script.src = 'https://maps.googleapis.com/maps/api/js?key=AIzaSyDFpFbxFFK7-daOKoIk9y_GB4m512Tii8M&callback=initMaps&libraries=geometry,places';
            script.async = true;
            script.defer = true;
            document.head.appendChild(script);
        }

        window.onload = loadGoogleMapsAPI;

        $('#map1Modal').on('shown.bs.modal', function () {
            if (!map1) {
                initMaps();
            }
        });

        $('#map2Modal').on('shown.bs.modal', function () {
            if (!map2) {
                initMaps();
            }
        });

        function calculateDistance() {
            const point1 = new google.maps.LatLng(lat1, lng1);
            const point2 = new google.maps.LatLng(lat2, lng2);
            const distanceInMeters = google.maps.geometry.spherical.computeDistanceBetween(point1, point2);
            const distanceInMiles = distanceInMeters / 1609.34;
            $('#distance').text(distanceInMiles.toFixed(2));
        }

    </script>

</body>
</html>
