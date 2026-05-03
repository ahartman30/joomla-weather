var json_data = %DATA%

var chartOptions_%CONTAINER_ID% = {
  chart: {
    renderTo: '%CONTAINER_ID%',
    alignTicks: false
  },
  title: {
    text: data_%CONTAINER_ID%.title
  },
  subtitle: {
    text: data_%CONTAINER_ID%.subtitle
  },
  credits: {
    enabled: true,
    text: 'Zum Wochenrückblick',
    href: 'http://www.heusenstamm-wetter.de/wetter-heusenstamm/aktuelle-werte#wochenrückblick',
    position: {
      align: 'center',
      x: 0,
      verticalAlign: 'bottom',
      y: 0
    },
    style: {
      cursor: 'pointer',
      color: '#909090',
      fontSize: '10px'
    }
  },
  xAxis: [{
    categories: data_%CONTAINER_ID%.xaxis,
    labels: {
      formatter: function() {
        return 	this.value == "heute" ? '<p style="color: red; font-weight: bold; font-size: small">heute</p>' : getDayOfWeek(this.value);
      }
    }
  }],
  yAxis: [{ // Temperatur
    allowDecimals: false,
    maxPadding: 0.1,
    labels: {
      formatter: function() {
        return this.value + data_%CONTAINER_ID%.yaxis_1_unit;
      },
      style: {
        color: '#000000'
      }
    },
    title: {
      text: ''
    }

  }, { // Niederschlag
    allowDecimals: false,
    maxPadding: 0.1,
    gridLineWidth: 0,
    title: {
      text: ''
    },
    labels: {
      formatter: function() {
        return this.value + data_%CONTAINER_ID%.yaxis_3_unit;
      },
      style: {
        color: '#32cd32'
      }
    },
    opposite: true
  }],
  tooltip: {
    borderColor: "#4572A7",
    borderWidth: 0,
    crosshairs: {
        width: 1,
        color: 'black',
        dashStyle: 'solid',
        zIndex: 100
    },
    shared: true,
    useHTML: true,
    formatter: function() {
      var max = this.points[1];
      var min = this.points[2];
      var rain = this.points[0];

      day = data_%CONTAINER_ID%.xaxis_tooltip[min.point.x];
      dayOfWeek = day == 'heute' ? '' : getDayOfWeek(data_%CONTAINER_ID%.xaxis_tooltip[min.point.x]) + ' ';
      var tooltip = '<span style="font-weight:bold">' + dayOfWeek + data_%CONTAINER_ID%.xaxis_tooltip[min.point.x] + "</span><br/>";
      tooltip += '<table><tr><td style="border:none;color:#FF0000;font-weight:bold">Maximum: </td><td style="border:none;color:#000000;font-weight:bold;text-align:right">' + max.y.toFixed(1) + '&nbsp;</td><td style="border:none;color:#000000;font-weight:bold;text-align:left">' + data_%CONTAINER_ID%.yaxis_1_unit + '</td></tr>';
      tooltip += '<tr><td style="border:none;color:#0000FF;font-weight:bold">Minimum: </td><td style="border:none;color:#000000;font-weight:bold;text-align:right">' + min.y.toFixed(1) + '&nbsp;</td><td style="border:none;color:#000000;font-weight:bold;text-align:left">' + data_%CONTAINER_ID%.yaxis_2_unit +  '</td></tr>';
      tooltip += '<tr><td style="border:none;color:#32cd32;font-weight:bold">Niederschlag:&nbsp;</td><td style="border:none;color:#000000;font-weight:bold;text-align:right">' + rain.y.toFixed(1) + '&nbsp;</td><td style="border:none;color:#000000;font-weight:bold;text-align:left">' + data_%CONTAINER_ID%.yaxis_3_unit +  '</td></tr></table>';

      return tooltip;
    }
  },
  legend: {
    layout: 'horizontal',
    align: 'center',
    verticalAlign: 'bottom',
  },
  series: [{ // Precip OBS
    name: data_%CONTAINER_ID%.yaxis_3_name,
    color: '#32cd32',
    type: 'column',
    grouping: false,
    yAxis: 1,
    legendIndex : 2,
    data: data_%CONTAINER_ID%.yaxis_3
  }, { // Max OBS
    name: data_%CONTAINER_ID%.yaxis_1_name,
    color: '#FF0000',
    type: 'spline',
    yAxis: 0,
    legendIndex : 0,
    marker: { symbol: 'circle' },
    data: data_%CONTAINER_ID%.yaxis_1
  }, { // Min OBS
    name: data_%CONTAINER_ID%.yaxis_2_name,
    type: 'spline',
    color: '#4572A7',
    yAxis: 0,
    legendIndex : 1,
    marker: { symbol: 'circle' },
    data: data_%CONTAINER_ID%.yaxis_2
  }, { // Precip FC
    name: data_%CONTAINER_ID%.yaxis_6_name,
    color: "#C2FFB7",
    type: 'column',
    grouping: false,
    yAxis: 1,
    legendIndex : 5,
    showInLegend: false,
    data: data_%CONTAINER_ID%.yaxis_6
  }, { // Max FC
    name: data_%CONTAINER_ID%.yaxis_4_name,
    color: '#FF0000',
    type: 'spline',
    yAxis: 0,
    legendIndex : 3,
    dashStyle: 'ShortDash',
    showInLegend: false,
    marker: { enabled: false },
    data: data_%CONTAINER_ID%.yaxis_4
  }, { // Min FC
    name: data_%CONTAINER_ID%.yaxis_5_name,
    type: 'spline',
    color: '#4572A7',
    yAxis: 0,
    legendIndex : 4,
    dashStyle: 'ShortDash',
    showInLegend: false,
    marker: { enabled: false },
    data: data_%CONTAINER_ID%.yaxis_5
  }],
  plotOptions: {
    series: {
      events: {
        legendItemClick: function(event) {
          return false;
        }
      }
    }
  }
}

jqChart_%CONTAINER_ID% = jQuery.noConflict();
function loadChart_%CONTAINER_ID%() {
  new Highcharts.Chart(chartOptions_%CONTAINER_ID%);
}
jqChart_%CONTAINER_ID%(document).ready(function() {
  loadChart_%CONTAINER_ID%();
});

function getDayOfWeek(dateAsString) {
  weekDays = new Array("So", "Mo", "Di", "Mi", "Do", "Fr", "Sa");
  year = dateAsString.slice(6, 10);
  month = dateAsString.slice(3, 5) - 1;
  day = dateAsString.slice(0, 2);
  date = new Date(year, month, day);
  dayOfWeek = date.getDay();
  return weekDays[dayOfWeek];
}
