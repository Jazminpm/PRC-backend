import json
import sys
import asyncio

from scrapers.utils import buscar

if __name__ == "__main__":
    data = json.loads(sys.argv[1])  # obtengo la query en el body
    asyncio.get_event_loop().run_until_complete(buscar(data["0"]))  # llamo a la funcion que me hace el scraping
