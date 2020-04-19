import json
import sys

from scrapers.utils import tu_tiempo

if __name__ == "__main__":
    data = json.loads(sys.argv[1])
    tu_tiempo(data["date_time"])
