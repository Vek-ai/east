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
<body>

    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#map1Modal">
        Open Map 1
    </button>

    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#map2Modal">
        Open Map 2
    </button>

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
        let lat1, lng1, lat2, lng2;

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

                if (lat2 && lng2) {
                    calculateDistance(lat1, lng1, lat2, lng2);
                }
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

                if (lat1 && lng1) {
                    calculateDistance(lat1, lng1, lat2, lng2);
                }
            });
        }

        function loadGoogleMapsAPI() {
            const script = document.createElement('script');
            script.src = 'https://maps.googleapis.com/maps/api/js?key=AIzaSyDRPyR0tSWQUm4sR0BwqDxSjVsdHXQvw7U&callback=initMaps&libraries=geometry';
            script.async = true;
            script.defer = true;
            document.head.appendChild(script);
        }

        $('#map1Modal').on('shown.bs.modal', function () {
            if (!map1) {
                loadGoogleMapsAPI();
            }
        });

        $('#map2Modal').on('shown.bs.modal', function () {
            if (!map2) {
                loadGoogleMapsAPI();
            }
        });

        function calculateDistance(lat1, lng1, lat2, lng2) {
            const point1 = new google.maps.LatLng(lat1, lng1);
            const point2 = new google.maps.LatLng(lat2, lng2);
            const distanceInMeters = google.maps.geometry.spherical.computeDistanceBetween(point1, point2);
            const distanceInMiles = distanceInMeters / 1609.34;
            alert("Distance between the points: " + distanceInMiles.toFixed(2) + " miles");
        }
    </script>

</body>
</html>
