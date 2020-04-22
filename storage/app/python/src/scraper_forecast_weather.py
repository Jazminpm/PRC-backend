import json
import sys

from scrapers.utils import weather_for_you

if __name__ == "__main__":
    data = json.loads(sys.argv[1])
    weather_for_you(airport_id = data["airport_id"], icao = data["0"])
