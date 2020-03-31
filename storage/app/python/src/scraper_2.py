import json
import sys

from scrapers.utils import select_date_tu_tiempo

if __name__ == "__main__":
    data = json.loads(sys.argv[1])
    select_date_tu_tiempo(int(data['day']), int(data['month']), int(data['year']))
