import json
import sys
import asyncio

from scrapers.utils import airports_name

if __name__ == "__main__":
    data = json.loads(sys.argv[1])
    asyncio.get_event_loop().run_until_complete(airports_name(data['country_id'].replace(' ', '-').lower()))
