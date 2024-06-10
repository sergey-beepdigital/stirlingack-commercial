(function( $ ) {

    /**
     * initMap
     *
     * Renders a Google Map onto the selected jQuery element
     *
     * @date    22/10/19
     * @since   5.8.6
     *
     * @param   jQuery $el The jQuery element.
     * @return  object The map instance.
     */
    function initMap( $map, properties ) {

        // Find marker elements within map.
        //var $markers = $el.find('.marker');

        // Create gerenic map.
        var mapArgs = {
            zoom: 16,
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            styles: window.globalMapStyles
        };

        var map = new google.maps.Map( $map[0], mapArgs );

        // Add markers.
        map.markers = [];
        properties.forEach(function(property){
            initMarker( property, map );
        });

        // Center map based on markers.
        centerMap( map );

        // Return map instance.
        return map;
    }

    /**
     * initMarker
     *
     * Creates a marker for the given jQuery element and map.
     *
     * @date    22/10/19
     * @since   5.8.6
     *
     * @param   jQuery $el The jQuery element.
     * @param   object The map instance.
     * @return  object The marker instance.
     */
    function initMarker( property, map ) {

        // Get position from marker.
        var lat = property.lat;
        var lng = property.lng;
        var latLng = {
            lat: parseFloat( lat ),
            lng: parseFloat( lng )
        };

        // Create marker instance.
        var marker = new google.maps.Marker({
            position : latLng,
            map: map,
            icon: sg_config.google_maps.marker_url
        });

        // Append to reference for later use.
        map.markers.push( marker );

        var markerHTML = '<div style="width:260px;padding: 0 15px;">';
        markerHTML += '<div class="row">';
        markerHTML += '<div class="col-12">';
        markerHTML += '<img src="' + property.image + '" style="max-width: 100%;">';
        markerHTML += '</div>';
        markerHTML += '<div class="col-12 mt-3">';
        markerHTML += '<h5 class="mb-3">' + property.title + '</h5>';
        markerHTML += '<a class="btn btn-primary text-uppercase" href="'+property.url+'">View Property</a>';
        markerHTML += '</div>';
        markerHTML += '</div>';
        markerHTML += '</div>';

        // Create info window.
        var infowindow = new google.maps.InfoWindow({
            content: markerHTML
        });

        // Show info window when marker is clicked.
        google.maps.event.addListener(marker, 'click', function() {
            infowindow.open( map, marker );
        });
    }

    /**
     * centerMap
     *
     * Centers the map showing all markers in view.
     *
     * @date    22/10/19
     * @since   5.8.6
     *
     * @param   object The map instance.
     * @return  void
     */
    function centerMap( map ) {

        // Create map boundaries from all map markers.
        var bounds = new google.maps.LatLngBounds();
        map.markers.forEach(function( marker ){
            bounds.extend({
                lat: marker.position.lat(),
                lng: marker.position.lng()
            });
        });

        // Case: Single marker.
        if( map.markers.length == 1 ){
            map.setCenter( bounds.getCenter() );

            // Case: Multiple markers.
        } else{
            map.fitBounds( bounds );
        }
    }

    // Render maps on page load.
    $(document).ready(function(){
        var $mapEl = $('#properties-map');

        if($mapEl.length && propertiesCoords.length) {
            initMap($mapEl, propertiesCoords);
        }

        /*$('.acf-map').each(function(){
            var map = initMap( $(this) );
        });*/
    });

})(jQuery);
