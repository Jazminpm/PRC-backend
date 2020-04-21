import json
import sys

from scrapers.utils import select_historical_date

if __name__ == "__main__":
    data = json.loads(sys.argv[1])
    select_historical_date(data['date'], data['0'], data['airport_id'])  # 0 -> URL
