#!/usr/bin/python3
# coding=UTF-8

import json
import argparse
from datetime import datetime, timedelta
from pytz import timezone

parser = argparse.ArgumentParser()
parser.add_argument("observation", type=str, help="JSON file with the observation data")
parser.add_argument("forecast", type=str, help="JSON file with the forecast data")
args = parser.parse_args()

with open(args.observation, encoding='ISO-8859-1') as file:
    observations = json.load(file)

with open(args.forecast, encoding='UTF-8') as file:
    forecasts = json.load(file)

# Get day of observation
gz = timezone('Europe/Berlin')
delta1day = timedelta(days=1)
observationDate = observations['xaxis'][-2]
observationDateGZ = gz.localize(datetime(int(observationDate[6:10]), int(observationDate[3:5]), int(observationDate[0:2]), 0, 0, 0, 0))
observationDateGZ += delta1day

# Get first day of forecast
forecastStartYearUTC = forecasts['forecast_step_year'][0]
forecastStartMonthUTC = forecasts['forecast_step_month'][0]
forecastStartDayUTC = forecasts['forecast_step_day'][0]
forecastStartDateGZ = gz.localize(datetime(int(forecastStartYearUTC), int(forecastStartMonthUTC), int(forecastStartDayUTC), 0, 0, 0, 0))
timeDeltaObservationForcast = abs(forecastStartDateGZ - observationDateGZ)

# Check seamless step from observation to forecast.
if timeDeltaObservationForcast > delta1day:
    exit(1)

# Check forecast UTC day starts before observation day GZ after midnight.
lowerBoundOffsetHours = 0
if forecastStartDateGZ < observationDateGZ:
    lowerBoundOffsetHours = 24

countForecasts = 9

# Get Tn (06 UTC), Tx (18 UTC), RR24 (00 UTC); note, RR24 is not consistent with GZ PTP in grid point table!
tn = list(map(lambda x: float(x), filter(lambda x: x != "---", forecasts['Tn_org'][30 + lowerBoundOffsetHours::24])))[:countForecasts]
tx = list(map(lambda x: float(x), filter(lambda x: x != "---", forecasts['Tx_org'][42 + lowerBoundOffsetHours::24])))[:countForecasts]
rr = list(map(lambda x: float(x), forecasts['RR24'][48 + lowerBoundOffsetHours::24]))[:countForecasts]

# Workaround: Append last UTC RR24, when forecast length exceeded after midnight.
if len(rr) == countForecasts - 1:
    rr.append(float(forecasts['RR24'][-1]))


chart = {
    'title': 'Rückblick und Vorhersage Heusenstamm',
    'subtitle': 'Wetterstation Heusenstamm und DWD-MOSMIX ' + forecasts['modelrun'],
    'xaxis': observations['xaxis'],
    'xaxis_tooltip': observations['xaxis_tooltip'],
    'yaxis_1': observations['yaxis_1'],
    'yaxis_1_unit': observations['yaxis_1_unit'],
    'yaxis_1_name': observations['yaxis_1_name'],
    'yaxis_2': observations['yaxis_3'],
    'yaxis_2_unit': observations['yaxis_3_unit'],
    'yaxis_2_name': observations['yaxis_3_name'],
    'yaxis_3': observations['yaxis_2'],
    'yaxis_3_unit': observations['yaxis_2_unit'],
    'yaxis_3_name': observations['yaxis_2_name']
}

# Add forecast dates to x-axis
for day in range(0, countForecasts):
    observationDateGZ += delta1day
    date = observationDateGZ.strftime('%d.%m.%Y')
    chart['xaxis'].append(date)
    chart['xaxis_tooltip'].append(date)

# Update current day
value = chart['yaxis_1'][-1]
chart['yaxis_1'][-1] = {"y": value, "marker": {"fillColor": "#FFFFFF", "lineColor": "#FF0000", "radius": 6, "lineWidth": 3}}
value = chart['yaxis_2'][-1]
chart['yaxis_2'][-1] = {"y": value, "marker": {"fillColor": "#FFFFFF", "lineColor": "#4572A7", "radius": 6, "lineWidth": 3}}

# Add forecast values
countObservations = len(observations['yaxis_1'])
nulls = [None] * countObservations
chart['yaxis_4'] = nulls + tx
chart['yaxis_5'] = nulls + tn
chart['yaxis_6'] = nulls + rr

print(json.dumps(chart, sort_keys=False, indent=2))
