import json
import sys

from scrapers.utils import tu_tiempo

if __name__ == "__main__":
    data = json.loads(sys.argv[1])
    tu_tiempo(str_date = data["date"], airport_id = data["airport_id"], icao = data["0"])
