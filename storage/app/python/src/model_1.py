import json
import sys

from models.utils import import_model

if __name__ == "__main__":
    data = json.loads(sys.argv[1])
    import_model(data['characteristic'].split(','), data['model'])
