import json
import sys
import pandas as pd  # CSV

from models.utils import import_model

if __name__ == "__main__":
    data = json.loads(sys.argv[1])
    import_model(data['algorithm'], pd.DataFrame.from_dict(data['0']))
