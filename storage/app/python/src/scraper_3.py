import json
import sys

from scrapers.utils import select_historical_date

if __name__ == "__main__":
    data = json.loads(sys.argv[1])
    select_historical_date(int(data['day']), int(data['month']), int(data['year']), data['url'])
