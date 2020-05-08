import json
import sys
import pandas as pd  # CSV

from models.utils import prediction

if __name__ == "__main__":
    data = json.loads(sys.argv[1])
    prediction(data['0'], data['1'], pd.DataFrame.from_dict(data['2']))
