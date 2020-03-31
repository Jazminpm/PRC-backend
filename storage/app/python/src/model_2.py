import json
import sys

from models.utils import prediction

if __name__ == "__main__":
    data = json.loads(sys.argv[1])
    prediction(data['characteristic'].split(','), data['model_name'], data['datetime'])
