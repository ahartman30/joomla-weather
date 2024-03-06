L.TileLayer.WarningMapWMS = L.TileLayer.WMS.extend({

  onAdd: function (map) {
    // Triggered when the layer is added to a map.
    //   Register a click listener, then do all the upstream WMS things
    L.TileLayer.WMS.prototype.onAdd.call(this, map);
    map.on('click', this.getFeatureInfoJsonp, this);
  },

  onRemove: function (map) {
    // Triggered when the layer is removed from a map.
    //   Unregister a click listener, then do all the upstream WMS things
    L.TileLayer.WMS.prototype.onRemove.call(this, map);
    map.off('click', this.getFeatureInfoJsonp, this);
  },

  getFeatureInfoJsonp: function (evt) {
    // Make an AJAX request to the server and hope for the best
    var url = this.getFeatureInfoUrl(evt.latlng),
      showResultsJson = L.Util.bind(this.showGetFeatureInfoJson, this);
    var theMap = this._map;
    weatherMap.fire("dataloading");
    jQuery.ajax({
      url: url,
      dataType: 'jsonp',
      jsonpCallback: 'parseResponse',
      success: function(data) {
        showResultsJson(evt.latlng, data);
      },
      complete: function(jqXHR, textStatus) {
        weatherMap.fire("dataload");
      }
    });
  },

  getFeatureInfoUrl: function (latlng) {
    // Construct a GetFeatureInfo request URL given a point
    var point = this._map.latLngToContainerPoint(latlng, this._map.getZoom()),
      size = this._map.getSize(),

      params = {
        request: 'GetFeatureInfo',
        service: 'WMS',
        srs: 'EPSG:4326',
        styles: this.wmsParams.styles,
        transparent: this.wmsParams.transparent,
        version: this.wmsParams.version,
        format: this.wmsParams.format,
        bbox: this._map.getBounds().toBBoxString(),
        height: size.y,
        width: size.x,
        layers: 'dwd:Warnungen_Gemeinden',
        query_layers: 'dwd:Warnungen_Gemeinden',
        info_format: 'text/javascript',
        // Nur ausgewählte Properties werden abgefragt - eine ungefilterte Antwort liefert eine Vielzahl weiterer Eigenschaften der Warnungen, analog zum Inhalt im CAP-Format
        propertyName: 'HEADLINE,ONSET,EXPIRES,NAME,ALTITUDE,THE_GEOM',
        // FEATURE_COUNT > 1 notwendig, um im Falle überlappender Warnungen alle Warnungen abzufragen
        FEATURE_COUNT: 50
      };

    params[params.version === '1.3.0' ? 'i' : 'x'] = point.x;
    params[params.version === '1.3.0' ? 'j' : 'y'] = point.y;

    return this._url + L.Util.getParamString(params, this._url, true);
  },

  showGetFeatureInfoJson: function (latlng, data) {
    if ( data.features[0] == null ) { return 0 };
    var name = data.features[0].properties.NAME;
    var content = "<h2>" + name + "</h2>";
    jQuery.each(data.features, function (i, item) {
      if (item.properties.NAME == name) { // On low zoom level maybe more than one area is hit, take only the first one.
        var o = new Date(item.properties.ONSET);
        var e = new Date(item.properties.EXPIRES);
        onset = ('0' + o.getDate()).slice(-2) + '.' + ('0' + (o.getMonth()+1)).slice(-2) + ". " + ('0' + (o.getHours())).slice(-2) + ":" + ('0' + (o.getMinutes())).slice(-2) + " Uhr";
        end = ('0' + e.getDate()).slice(-2) + '.' + ('0' + (e.getMonth()+1)).slice(-2) + ". " + ('0' + (e.getHours())).slice(-2) + ":" + ('0' + (e.getMinutes())).slice(-2) + " Uhr";
        var altitudeInFeet = item.properties.ALTITUDE;
        content += "<p><table><tr><td><b>" + item.properties.HEADLINE + "</b></td></tr>";
        if (altitudeInFeet > 0) {
          var altitudeInMeters = Math.round(altitudeInFeet / 3.28084);
          content += "<tr><td><b><i>oberhalb " + altitudeInMeters + " m</i></b></td></tr>";
        }
        content += "<tr><td>" + onset + "&nbsp;&nbsp;&minus;&nbsp;&nbsp;" + end + "</td></tr>";
        content += "</table></p>";        
      }
    });
    
    var weatherMap = this._map;

    L.responsivePopup({ maxWidth: 200})
      .setLatLng(latlng)
      .setContent(content)
      .openOn(weatherMap);
      
    var polygonLayer = L.geoJSON().addTo(weatherMap);
    polygonLayer.addData(data.features[0]);
    weatherMap.on('popupclose', function(e) {
      weatherMap.removeLayer(polygonLayer);
    });

  }
});

L.tileLayer.warningMapWms = function (url, options) {
  return new L.TileLayer.WarningMapWMS(url, options);
};
